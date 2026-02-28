<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\SearchTermResource;
use App\Jobs\AddKeywordToAdGroupJob;
use App\Jobs\AddNegativeKeywordJob;
use App\Jobs\SyncSearchTermStatsJob;
use App\Models\AdGroup;
use App\Models\SearchTerm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SearchTermApiController extends BaseApiController
{
    /**
     * Listar todos os termos de pesquisa.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SearchTerm::query();

        // Filtros
        if ($request->filled('search_term')) {
            $query->where('search_term', 'like', '%' . $request->input('search_term') . '%');
        }

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->input('campaign_id'));
        }

        if ($request->filled('campaign_name')) {
            $query->where('campaign_name', $request->input('campaign_name'));
        }

        if ($request->filled('ad_group_id')) {
            $query->where('ad_group_id', $request->input('ad_group_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('match_type')) {
            $query->where('match_type', $request->input('match_type'));
        }

        if ($request->filled('min_impressions')) {
            $query->where('impressions', '>=', (int) $request->input('min_impressions'));
        }

        if ($request->filled('min_clicks')) {
            $query->where('clicks', '>=', (int) $request->input('min_clicks'));
        }

        if ($request->filled('min_cost')) {
            $query->where('cost_micros', '>=', (float) $request->input('min_cost') * 1000000);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('first_seen_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('first_seen_at', '<=', $request->input('date_to'));
        }

        // Ordenação
        $sortBy = $request->input('sort_by', 'first_seen_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $allowedSortColumns = ['impressions', 'clicks', 'cost_micros', 'first_seen_at', 'created_at', 'search_term'];
        
        if (in_array($sortBy, $allowedSortColumns)) {
            $query->orderBy($sortBy, $sortDirection === 'asc' ? 'asc' : 'desc');
        }

        // Paginação
        $perPage = $request->input('per_page', 50);
        $perPage = min(max((int) $perPage, 1), 1000);

        $searchTerms = $query->paginate($perPage);

        return $this->paginatedResponse($searchTerms, SearchTermResource::class);
    }

    /**
     * Exibir um termo de pesquisa específico.
     */
    public function show(SearchTerm $searchTerm): JsonResponse
    {
        return $this->successResponse(new SearchTermResource($searchTerm));
    }

    /**
     * Atualizar estatísticas de um termo de pesquisa.
     */
    public function refreshStats(SearchTerm $searchTerm): JsonResponse
    {
        try {
            $job = new SyncSearchTermStatsJob($searchTerm);
            $googleAdsClient = app(\Google\Ads\GoogleAds\Lib\V20\GoogleAdsClient::class);
            $quotaService = app(\App\Services\GoogleAdsQuotaService::class);
            
            $updatedSearchTerm = $job->handleSynchronous($googleAdsClient, $quotaService);

            return $this->successResponse(
                new SearchTermResource($updatedSearchTerm),
                'Estatísticas atualizadas com sucesso!'
            );
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar estatísticas: ' . $e->getMessage());
            return $this->errorResponse('Erro ao atualizar estatísticas: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Adicionar termo como palavra-chave negativa.
     */
    public function addAsNegative(Request $request, SearchTerm $searchTerm): JsonResponse
    {
        $validated = $request->validate([
            'match_type' => 'required|in:broad,phrase,exact',
            'reason' => 'nullable|string|max:1000',
        ]);

        $matchType = $validated['match_type'];
        $reason = $validated['reason'] ?? null;
        $listId = config('googleads.default_negative_list_id');

        if (empty($listId)) {
            return $this->errorResponse('Lista de palavras-chave negativas não configurada.', 400);
        }

        try {
            $apiToken = $request->attributes->get('api_token');
            $userId = $apiToken?->created_by_id;

            $job = AddNegativeKeywordJob::dispatch(
                $searchTerm->search_term,
                $listId,
                $matchType,
                $reason,
                $userId
            );

            return $this->successResponse([
                'job_id' => $job,
                'term' => $searchTerm->search_term,
                'match_type' => $matchType,
                'list_id' => $listId,
            ], 'Termo adicionado à fila como palavra-chave negativa.');
        } catch (\Exception $e) {
            Log::error('Erro ao adicionar termo negativo: ' . $e->getMessage());
            return $this->errorResponse('Erro ao adicionar termo negativo: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Adicionar termo como palavra-chave positiva.
     */
    public function addAsPositive(Request $request, SearchTerm $searchTerm): JsonResponse
    {
        $validated = $request->validate([
            'ad_group_id' => 'required|exists:ad_groups,id',
            'match_type' => 'required|in:broad,phrase,exact',
        ]);

        $adGroup = AdGroup::findOrFail($validated['ad_group_id']);

        try {
            $apiToken = $request->attributes->get('api_token');
            $userId = $apiToken?->created_by_id;

            $job = AddKeywordToAdGroupJob::dispatch(
                $adGroup->google_ad_group_id,
                $searchTerm->search_term,
                $validated['match_type'],
                $userId
            );

            return $this->successResponse([
                'job_id' => $job,
                'term' => $searchTerm->search_term,
                'ad_group' => $adGroup->name,
                'match_type' => $validated['match_type'],
            ], 'Termo adicionado à fila como palavra-chave positiva.');
        } catch (\Exception $e) {
            Log::error('Erro ao adicionar termo positivo: ' . $e->getMessage());
            return $this->errorResponse('Erro ao adicionar termo positivo: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Negar múltiplos termos em lote.
     */
    public function batchNegate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'terms' => 'required|array',
            'terms.*.id' => 'required|integer|exists:search_terms,id',
            'terms.*.reason' => 'nullable|string',
            'terms.*.rationale' => 'nullable|string',
            'match_type' => 'required|in:exact,phrase,broad',
        ]);

        $matchType = $validated['match_type'];
        $listId = config('googleads.default_negative_list_id');
        $apiToken = $request->attributes->get('api_token');
        $userId = $apiToken?->created_by_id;

        if (empty($listId)) {
            return $this->errorResponse('Lista de palavras-chave negativas não configurada.', 400);
        }

        $results = [];

        foreach ($validated['terms'] as $term) {
            $searchTerm = SearchTerm::find($term['id']);
            
            if ($searchTerm) {
                AddNegativeKeywordJob::dispatch(
                    $searchTerm->search_term,
                    $listId,
                    $matchType,
                    $term['reason'] ?? $term['rationale'] ?? null,
                    $userId
                );
                
                $results[] = [
                    'id' => $searchTerm->id,
                    'search_term' => $searchTerm->search_term,
                    'status' => 'queued'
                ];
            }
        }

        return $this->successResponse($results, count($results) . ' termo(s) adicionado(s) à fila.');
    }

    /**
     * Obter estatísticas agregadas dos termos.
     */
    public function stats(Request $request): JsonResponse
    {
        $query = SearchTerm::query();

        // Aplicar filtros de data
        if ($request->filled('date_from')) {
            $query->whereDate('first_seen_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('first_seen_at', '<=', $request->input('date_to'));
        }

        $stats = [
            'total_terms' => (clone $query)->count(),
            'total_impressions' => (clone $query)->sum('impressions'),
            'total_clicks' => (clone $query)->sum('clicks'),
            'total_cost_micros' => (clone $query)->sum('cost_micros'),
            'avg_ctr' => (clone $query)->avg('ctr'),
            'by_status' => (clone $query)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status'),
            'by_match_type' => (clone $query)
                ->selectRaw('match_type, COUNT(*) as count')
                ->groupBy('match_type')
                ->pluck('count', 'match_type'),
            'top_campaigns' => (clone $query)
                ->selectRaw('campaign_name, COUNT(*) as count, SUM(cost_micros) as total_cost')
                ->groupBy('campaign_name')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
        ];

        return $this->successResponse($stats);
    }
}
