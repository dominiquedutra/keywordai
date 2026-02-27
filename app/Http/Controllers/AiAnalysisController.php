<?php

namespace App\Http\Controllers;

use App\Jobs\AddNegativeKeywordJob;
use App\Models\SearchTerm;
use App\Services\AiAnalysisService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiAnalysisController extends Controller
{
    /**
     * O serviço de análise de IA.
     *
     * @var \App\Services\AiAnalysisService
     */
    protected $aiAnalysisService;

    /**
     * Cria uma nova instância do controlador.
     *
     * @param \App\Services\AiAnalysisService $aiAnalysisService
     * @return void
     */
    public function __construct(AiAnalysisService $aiAnalysisService)
    {
        $this->aiAnalysisService = $aiAnalysisService;
    }

    /**
     * Exibe a página de análise de IA.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Obter os modelos de IA disponíveis (com nome real do modelo configurado)
        $aiModels = [
            'gemini' => 'Gemini — ' . (setting('ai_gemini_model') ?: 'gemini-2.0-flash'),
            'openai' => 'OpenAI — ' . (setting('ai_openai_model') ?: 'gpt-4o-mini'),
            'openrouter' => 'OpenRouter — ' . (setting('ai_openrouter_model') ?: 'google/gemini-2.0-flash-001'),
        ];

        // Obter o modelo padrão
        $defaultModel = 'gemini';

        // Obter o match type padrão para negativação
        $defaultMatchType = setting('negative_keyword_default_match_type', 'broad');

        return view('ai_analysis.index', [
            'aiModels' => $aiModels,
            'defaultModel' => $defaultModel,
            'defaultMatchType' => $defaultMatchType
        ]);
    }

    /**
     * Analisa termos de pesquisa com IA.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function analyze(Request $request)
    {
        // Validar a requisição
        $validated = $request->validate([
            'analysis_type' => 'required|in:date,top',
            'date' => 'required_if:analysis_type,date|date_format:Y-m-d',
            'min_impressions' => 'nullable|integer|min:0',
            'min_clicks' => 'nullable|integer|min:0',
            'min_cost' => 'nullable|numeric|min:0',
            'model' => 'required|in:gemini,openai,openrouter',
            'limit' => 'required|integer|min:1|max:1000',
        ]);

        // Preparar os filtros
        $filters = [
            'min_impressions' => $validated['min_impressions'] ?? 0,
            'min_clicks' => $validated['min_clicks'] ?? 0,
            'min_cost' => $validated['min_cost'] ?? 0,
        ];

        // Preparar a data (se aplicável)
        $date = null;
        if ($validated['analysis_type'] === 'date' && isset($validated['date'])) {
            $date = Carbon::createFromFormat('Y-m-d', $validated['date']);
        }

        // Chamar o serviço para analisar os termos
        $result = $this->aiAnalysisService->analyze(
            $validated['model'],
            $validated['limit'],
            $filters,
            $date
        );

        // Retornar os resultados como JSON
        return response()->json($result);
    }

    /**
     * Preview: builds the prompt and returns it without calling the AI API.
     */
    public function preview(Request $request)
    {
        $validated = $request->validate([
            'analysis_type' => 'required|in:date,top',
            'date' => 'required_if:analysis_type,date|date_format:Y-m-d',
            'min_impressions' => 'nullable|integer|min:0',
            'min_clicks' => 'nullable|integer|min:0',
            'min_cost' => 'nullable|numeric|min:0',
            'model' => 'required|in:gemini,openai,openrouter',
            'limit' => 'required|integer|min:1|max:1000',
        ]);

        $filters = [
            'min_impressions' => $validated['min_impressions'] ?? 0,
            'min_clicks' => $validated['min_clicks'] ?? 0,
            'min_cost' => $validated['min_cost'] ?? 0,
        ];

        $date = null;
        if ($validated['analysis_type'] === 'date' && isset($validated['date'])) {
            $date = Carbon::createFromFormat('Y-m-d', $validated['date']);
        }

        $model = $validated['model'];
        $searchTerms = $this->aiAnalysisService->collectSearchTerms($date, $validated['limit'], $filters);

        if ($searchTerms->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum termo de pesquisa encontrado com os critérios especificados.',
            ]);
        }

        $negativeKeywords = $this->aiAnalysisService->collectNegativeKeywords();
        $positiveKeywords = $this->aiAnalysisService->collectPositiveKeywords();
        $globalInstructions = setting('ai_global_custom_instructions', '');
        $modelSpecificInstructions = setting("ai_{$model}_custom_instructions", '');

        $prompt = $this->aiAnalysisService->buildPrompt(
            $searchTerms, $negativeKeywords, $positiveKeywords,
            $globalInstructions, $modelSpecificInstructions, $date
        );

        $modelName = setting("ai_{$model}_model") ?: config("ai.models.{$model}.model_name", $model);

        return response()->json([
            'success' => true,
            'terms_count' => $searchTerms->count(),
            'prompt' => $prompt,
            'prompt_size' => strlen($prompt),
            'model' => $model,
            'model_name' => $modelName,
        ]);
    }

    /**
     * Adiciona termos de pesquisa selecionados como palavras-chave negativas.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchNegate(Request $request)
    {
        // Validar a requisição
        $validated = $request->validate([
            'terms' => 'required|array',
            'terms.*.id' => 'required|integer|exists:search_terms,id',
            'terms.*.rationale' => 'nullable|string',
            'match_type' => 'required|in:exact,phrase,broad',
        ]);

        $matchType = $validated['match_type'];
        $userId = Auth::id();
        $results = [];

        // Para cada termo selecionado
        foreach ($validated['terms'] as $term) {
            $searchTerm = SearchTerm::find($term['id']);
            
            if ($searchTerm) {
                // Disparar o job para adicionar a palavra-chave negativa
                AddNegativeKeywordJob::dispatch(
                    $searchTerm->search_term,
                    config('googleads.default_negative_list_id'),
                    $matchType,
                    $term['rationale'] ?? null,
                    $userId
                );
                
                $results[] = [
                    'id' => $searchTerm->id,
                    'search_term' => $searchTerm->search_term,
                    'status' => 'queued'
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($results) . ' termo(s) adicionado(s) como palavra(s)-chave negativa(s).',
            'results' => $results
        ]);
    }
}
