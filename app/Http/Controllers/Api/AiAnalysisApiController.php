<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\AiAnalysisResource;
use App\Jobs\AddNegativeKeywordJob;
use App\Models\SearchTerm;
use App\Services\AiAnalysisService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiAnalysisApiController extends BaseApiController
{
    /**
     * O serviço de análise de IA.
     */
    protected AiAnalysisService $aiAnalysisService;

    /**
     * Construtor.
     */
    public function __construct(AiAnalysisService $aiAnalysisService)
    {
        $this->aiAnalysisService = $aiAnalysisService;
    }

    /**
     * Analisar termos de pesquisa com IA.
     */
    public function analyze(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'analysis_type' => 'required|in:date,top,custom',
            'date' => 'required_if:analysis_type,date|date_format:Y-m-d',
            'min_impressions' => 'nullable|integer|min:0',
            'min_clicks' => 'nullable|integer|min:0',
            'min_cost' => 'nullable|numeric|min:0',
            'model' => 'required|in:gemini,openai,perplexity',
            'limit' => 'nullable|integer|min:1|max:200',
            'filters' => 'nullable|array',
        ]);

        $limit = $validated['limit'] ?? 50;

        try {
            $filters = [
                'min_impressions' => $validated['min_impressions'] ?? 0,
                'min_clicks' => $validated['min_clicks'] ?? 0,
                'min_cost' => $validated['min_cost'] ?? 0,
            ];

            $date = null;
            if ($validated['analysis_type'] === 'date' && isset($validated['date'])) {
                $date = Carbon::createFromFormat('Y-m-d', $validated['date']);
            }

            $result = $this->aiAnalysisService->analyze(
                $validated['model'],
                $limit,
                $filters,
                $date
            );

            return $this->successResponse(
                new AiAnalysisResource($result),
                'Análise concluída com sucesso.'
            );
        } catch (\Exception $e) {
            Log::error('Erro na análise de IA: ' . $e->getMessage());
            return $this->errorResponse('Erro na análise: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Sugestões de negativação baseadas em análise.
     */
    public function suggestNegatives(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'model' => 'required|in:gemini,openai,perplexity',
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d',
            'min_impressions' => 'nullable|integer|min:0',
            'limit' => 'nullable|integer|min:1|max:100',
            'auto_negate' => 'nullable|boolean',
            'match_type' => 'required_with:auto_negate|in:broad,phrase,exact',
        ]);

        try {
            $query = SearchTerm::query();

            if ($request->filled('date_from')) {
                $query->whereDate('first_seen_at', '>=', $request->input('date_from'));
            }

            if ($request->filled('date_to')) {
                $query->whereDate('first_seen_at', '<=', $request->input('date_to'));
            }

            if ($request->filled('min_impressions')) {
                $query->where('impressions', '>=', $request->input('min_impressions'));
            }

            $limit = $validated['limit'] ?? 50;
            $terms = $query->limit($limit)->get();

            if ($terms->isEmpty()) {
                return $this->errorResponse('Nenhum termo encontrado para análise.', 404);
            }

            // Preparar dados para análise
            $termsData = $terms->map(function ($term) {
                return [
                    'id' => $term->id,
                    'search_term' => $term->search_term,
                    'impressions' => $term->impressions,
                    'clicks' => $term->clicks,
                    'cost_micros' => $term->cost_micros,
                    'ctr' => $term->ctr,
                    'campaign' => $term->campaign_name,
                    'ad_group' => $term->ad_group_name,
                ];
            });

            // Análise de sugestão
            $analysis = $this->aiAnalysisService->analyzeTermsForNegation(
                $validated['model'],
                $termsData->toArray()
            );

            // Auto-negatar se solicitado
            $negated = [];
            if ($request->boolean('auto_negate') && !empty($analysis['suggestions'])) {
                $listId = config('googleads.default_negative_list_id');
                $apiToken = $request->attributes->get('api_token');
                $userId = $apiToken?->created_by_id;

                foreach ($analysis['suggestions'] as $suggestion) {
                    if ($suggestion['should_negate'] ?? false) {
                        $job = AddNegativeKeywordJob::dispatch(
                            $suggestion['search_term'],
                            $listId,
                            $validated['match_type'],
                            $suggestion['rationale'] ?? 'Auto-negated via API analysis',
                            $userId
                        );
                        $negated[] = [
                            'term' => $suggestion['search_term'],
                            'job_id' => $job,
                        ];
                    }
                }
            }

            return $this->successResponse([
                'analysis' => $analysis,
                'terms_analyzed' => $terms->count(),
                'auto_negated' => $negated,
                'model' => $validated['model'],
            ]);
        } catch (\Exception $e) {
            Log::error('Erro nas sugestões de negativação: ' . $e->getMessage());
            return $this->errorResponse('Erro na análise: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obter modelos de IA disponíveis.
     */
    public function availableModels(): JsonResponse
    {
        $models = [
            'gemini' => [
                'name' => 'Gemini (Google)',
                'model' => config('app.ai_gemini_model', 'gemini-2.5-flash-preview-04-17'),
                'available' => !empty(config('app.ai_gemini_api_key')),
            ],
            'openai' => [
                'name' => 'OpenAI (GPT)',
                'model' => config('app.ai_openai_model', 'gpt-4o'),
                'available' => !empty(config('app.ai_openai_api_key')),
            ],
            'perplexity' => [
                'name' => 'Perplexity',
                'model' => config('app.ai_perplexity_model', 'sonar-medium-online'),
                'available' => !empty(config('app.ai_perplexity_api_key')),
            ],
        ];

        return $this->successResponse($models);
    }
}
