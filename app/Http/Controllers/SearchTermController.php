<?php

namespace App\Http\Controllers;

use App\Jobs\SyncSearchTermStatsJob;
use App\Models\AdGroup;
use App\Models\SearchTerm;
use App\Services\GoogleAdsQuotaService;
use Google\Ads\GoogleAds\Lib\V19\GoogleAdsClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchTermController extends Controller
{
    /**
     * Atualiza as estatísticas de um termo de pesquisa específico de forma síncrona.
     *
     * @param \App\Models\SearchTerm $searchTerm
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshStats(SearchTerm $searchTerm): JsonResponse
    {
        try {
            // Instanciar o job
            $job = new SyncSearchTermStatsJob($searchTerm);
            
            // Resolver as dependências
            $googleAdsClient = app(GoogleAdsClient::class);
            $quotaService = app(GoogleAdsQuotaService::class);
            
            // Executar o job de forma síncrona
            $updatedSearchTerm = $job->handleSynchronous($googleAdsClient, $quotaService);
            
            // Preparar os dados para retornar ao frontend
            $data = [
                'id' => $updatedSearchTerm->id,
                'impressions' => $updatedSearchTerm->impressions,
                'clicks' => $updatedSearchTerm->clicks,
                'cost_micros' => $updatedSearchTerm->cost_micros,
                'ctr' => $updatedSearchTerm->ctr,
                'status' => $updatedSearchTerm->status,
                'statistics_synced_at' => $updatedSearchTerm->statistics_synced_at,
                'formatted' => [
                    'impressions' => number_format($updatedSearchTerm->impressions, 0, ',', '.'),
                    'clicks' => number_format($updatedSearchTerm->clicks, 0, ',', '.'),
                    'cost' => 'R$ ' . number_format($updatedSearchTerm->cost_micros / 1000000, 2, ',', '.'),
                    'ctr' => number_format($updatedSearchTerm->ctr, 0, ',', '.') . '%',
                    'statistics_synced_at' => $updatedSearchTerm->statistics_synced_at ? $updatedSearchTerm->statistics_synced_at->format('d/m/Y H:i:s') : '-'
                ]
            ];
            
            // Retornar resposta de sucesso com os dados atualizados
            return response()->json([
                'success' => true,
                'message' => 'Estatísticas atualizadas com sucesso!',
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            // Registrar o erro
            \Log::error('Erro ao atualizar estatísticas do termo: ' . $e->getMessage(), [
                'term_id' => $searchTerm->id,
                'term' => $searchTerm->search_term,
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Retornar resposta de erro
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar estatísticas: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        // Get filter parameters from the request
        $searchTermFilter = $request->input('term');
        $campaignNameFilter = $request->input('campaign_name');
        $adGroupNameFilter = $request->input('ad_group_name');
        $statusFilter = $request->input('status');
        $matchTypeFilter = $request->input('match_type');
        $keywordTextFilter = $request->input('keyword_text');
        $minImpressions = $request->input('min_impressions');
        $minClicks = $request->input('min_clicks');
        $minCost = $request->input('min_cost');
        $sortBy = $request->input('sort_by');
        $sortDirection = $request->input('sort_direction', 'desc'); // Default DESC

        // Build the query
        $query = SearchTerm::query();

        // Apply filters
        $query->when($searchTermFilter, function ($q) use ($searchTermFilter) {
            return $q->where('search_term', 'like', '%' . $searchTermFilter . '%');
        });

        // Apply new numeric filters
        $query->when($request->filled('min_impressions'), function ($q) use ($minImpressions) {
            return $q->where('impressions', '>', (int)$minImpressions);
        });

        $query->when($request->filled('min_clicks'), function ($q) use ($minClicks) {
            return $q->where('clicks', '>', (int)$minClicks);
        });

        $query->when($request->filled('min_cost'), function ($q) use ($minCost) {
            // Converter o custo de reais para micros (multiplicar por 1.000.000)
            return $q->where('cost_micros', '>', (float)$minCost * 1000000);
        });

        $query->when($campaignNameFilter, function ($q) use ($campaignNameFilter) {
            return $q->where('campaign_name', $campaignNameFilter);
        });

        $query->when($adGroupNameFilter, function ($q) use ($adGroupNameFilter) {
            return $q->where('ad_group_name', $adGroupNameFilter);
        });

        // Apply new filters
        $query->when($statusFilter, function ($q) use ($statusFilter) {
            if ($statusFilter === 'Added/Excluded') {
                return $q->whereIn('status', ['ADDED', 'EXCLUDED']);
            }
            return $q->where('status', $statusFilter);
        });

        $query->when($matchTypeFilter, function ($q) use ($matchTypeFilter) {
            return $q->where('match_type', $matchTypeFilter);
        });

        $query->when($keywordTextFilter, function ($q) use ($keywordTextFilter) {
            return $q->where('keyword_text', 'like', '%' . $keywordTextFilter . '%');
        });

        // Apply dynamic sorting
        $allowedSortColumns = ['impressions', 'clicks', 'cost_micros', 'first_seen_at'];
        if (in_array($sortBy, $allowedSortColumns)) {
            $query->orderBy($sortBy, $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            // Default sort if no valid sort_by is provided
            $query->orderBy('first_seen_at', 'desc');
        }
        // Add consistent secondary sort
        $query->orderBy('id', 'desc');

        // Get per_page parameter from request, default to 50 if not provided or invalid
        $perPage = $request->input('per_page', 50);
        
        // Validate per_page is one of the allowed values
        $allowedPerPageValues = [50, 100, 250, 500];
        if (!in_array($perPage, $allowedPerPageValues)) {
            $perPage = 50; // Default to 50 if invalid
        }
        
        // Paginate results with the selected per_page value
        $searchTerms = $query->paginate($perPage);

        // Get distinct values for filter dropdowns
        $campaignNames = SearchTerm::select('campaign_name')
                                    ->distinct()
                                    ->orderBy('campaign_name')
                                    ->pluck('campaign_name');

        $adGroupNames = SearchTerm::select('ad_group_name')
                                   ->whereNotNull('ad_group_name') // Ensure we don't get nulls if any exist
                                   ->distinct()
                                   ->orderBy('ad_group_name')
                                   ->pluck('ad_group_name');

        // Get distinct values for new filter dropdowns
        $statuses = SearchTerm::select('status')
                              ->whereNotNull('status')
                              ->distinct()
                              ->orderBy('status')
                              ->pluck('status');

        $matchTypes = SearchTerm::select('match_type')
                                ->whereNotNull('match_type')
                                ->distinct()
                                ->orderBy('match_type')
                                ->pluck('match_type');

        // Buscar todos os grupos de anúncios ativos com suas campanhas ativas do tipo SEARCH
        $adGroups = AdGroup::with('campaign')
            ->where('status', 'ENABLED') // Garante que o Grupo de Anúncios esteja ativo
            ->whereHas('campaign', function ($query) { // Adiciona condição na Campanha relacionada
                $query->where('status', 'ENABLED') // Garante que a Campanha também esteja ativa
                      ->where('advertising_channel_type', 'SEARCH'); // Garante que a Campanha seja do tipo SEARCH
            })
            ->orderBy('name')
            ->get()
            ->map(function ($adGroup) {
                return [
                    'id' => $adGroup->id,
                    'text' => $adGroup->formatted_name,
                    'google_ad_group_id' => $adGroup->google_ad_group_id,
                ];
            });

        // Return the view with data
        return view('search_terms.index', [
            'searchTerms' => $searchTerms,
            'campaignNames' => $campaignNames,
            'adGroupNames' => $adGroupNames,
            'statuses' => $statuses,
            'matchTypes' => $matchTypes,
            'filters' => $request->only(['term', 'campaign_name', 'ad_group_name', 'status', 'match_type', 'keyword_text', 'min_impressions', 'min_clicks', 'min_cost', 'sort_by', 'sort_direction', 'per_page']), // Pass all filters back to view
            'adGroups' => $adGroups, // Passar a lista de grupos de anúncios para a view
            'perPage' => $perPage, // Pass the current per_page value
            'allowedPerPageValues' => $allowedPerPageValues, // Pass the allowed values
        ]);
    }
}
