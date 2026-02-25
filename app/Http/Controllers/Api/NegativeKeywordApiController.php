<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\NegativeKeywordResource;
use App\Jobs\AddNegativeKeywordJob;
use App\Models\NegativeKeyword;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NegativeKeywordApiController extends BaseApiController
{
    /**
     * Listar todas as palavras-chave negativas.
     */
    public function index(Request $request): JsonResponse
    {
        $query = NegativeKeyword::with('createdBy');

        if ($request->filled('keyword')) {
            $query->where('keyword', 'like', '%' . $request->input('keyword') . '%');
        }

        if ($request->filled('match_type')) {
            $query->where('match_type', $request->input('match_type'));
        }

        if ($request->filled('list_id')) {
            $query->where('list_id', $request->input('list_id'));
        }

        if ($request->filled('created_by')) {
            $query->where('created_by_id', $request->input('created_by'));
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $perPage = $request->input('per_page', 50);
        $perPage = min(max((int) $perPage, 1), 1000);

        $keywords = $query->paginate($perPage);

        return $this->paginatedResponse($keywords, NegativeKeywordResource::class);
    }

    /**
     * Exibir uma palavra-chave negativa específica.
     */
    public function show(NegativeKeyword $negativeKeyword): JsonResponse
    {
        return $this->successResponse(new NegativeKeywordResource($negativeKeyword->load('createdBy')));
    }

    /**
     * Criar nova palavra-chave negativa.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'keyword' => 'required|string|max:255',
            'match_type' => 'required|in:broad,phrase,exact',
            'reason' => 'nullable|string|max:1000',
            'list_id' => 'nullable|string',
        ]);

        $listId = $validated['list_id'] ?? config('googleads.default_negative_list_id');

        if (empty($listId)) {
            return $this->errorResponse('Lista de palavras-chave negativas não configurada.', 400);
        }

        try {
            $apiToken = $request->attributes->get('api_token');
            $userId = $apiToken?->created_by_id;

            $job = AddNegativeKeywordJob::dispatch(
                $validated['keyword'],
                $listId,
                $validated['match_type'],
                $validated['reason'] ?? null,
                $userId
            );

            return $this->successResponse([
                'job_id' => $job,
                'keyword' => $validated['keyword'],
                'match_type' => $validated['match_type'],
                'list_id' => $listId,
            ], 'Palavra-chave negativa adicionada à fila.', 201);
        } catch (\Exception $e) {
            Log::error('Erro ao criar palavra-chave negativa: ' . $e->getMessage());
            return $this->errorResponse('Erro ao criar palavra-chave negativa: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Criar múltiplas palavras-chave negativas.
     */
    public function batchStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'keywords' => 'required|array',
            'keywords.*.keyword' => 'required|string|max:255',
            'keywords.*.match_type' => 'required|in:broad,phrase,exact',
            'keywords.*.reason' => 'nullable|string|max:1000',
            'list_id' => 'nullable|string',
        ]);

        $listId = $validated['list_id'] ?? config('googleads.default_negative_list_id');

        if (empty($listId)) {
            return $this->errorResponse('Lista de palavras-chave negativas não configurada.', 400);
        }

        $apiToken = $request->attributes->get('api_token');
        $userId = $apiToken?->created_by_id;

        $results = [];

        foreach ($validated['keywords'] as $keywordData) {
            try {
                $job = AddNegativeKeywordJob::dispatch(
                    $keywordData['keyword'],
                    $listId,
                    $keywordData['match_type'],
                    $keywordData['reason'] ?? null,
                    $userId
                );

                $results[] = [
                    'keyword' => $keywordData['keyword'],
                    'match_type' => $keywordData['match_type'],
                    'status' => 'queued',
                    'job_id' => $job,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'keyword' => $keywordData['keyword'],
                    'match_type' => $keywordData['match_type'],
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        $queued = collect($results)->where('status', 'queued')->count();

        return $this->successResponse($results, "{$queued} palavra(s)-chave adicionada(s) à fila.", 201);
    }

    /**
     * Obter estatísticas.
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total' => NegativeKeyword::count(),
            'by_match_type' => NegativeKeyword::selectRaw('match_type, COUNT(*) as count')
                ->groupBy('match_type')
                ->pluck('count', 'match_type'),
            'by_list' => NegativeKeyword::selectRaw('list_id, COUNT(*) as count')
                ->groupBy('list_id')
                ->pluck('count', 'list_id'),
            'recent' => NegativeKeyword::where('created_at', '>=', now()->subDays(7))
                ->count(),
        ];

        return $this->successResponse($stats);
    }
}
