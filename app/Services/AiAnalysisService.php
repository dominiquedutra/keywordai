<?php

namespace App\Services;

use App\Models\NegativeKeyword;
use App\Models\SearchTerm;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAnalysisService
{
    /**
     * Analisa termos de pesquisa com IA.
     *
     * @param string $model Nome do modelo de IA (gemini, openai, openrouter)
     * @param int $limit Limite de termos a serem analisados
     * @param array $filters Filtros adicionais (min_impressions, min_clicks, min_cost)
     * @param Carbon|null $date Data específica para análise (null para análise por custo)
     * @return array Resultados da análise e métricas da API
     */
    public function analyze(string $model, int $limit = 50, array $filters = [], ?Carbon $date = null): array
    {
        // Validar o modelo
        $validModels = ['gemini', 'openai', 'openrouter'];
        if (!in_array($model, $validModels)) {
            throw new \InvalidArgumentException("Modelo inválido: '{$model}'. Use um dos seguintes: " . implode(', ', $validModels));
        }

        // Obter a chave de API do modelo selecionado (DB primeiro, .env fallback)
        $apiKey = setting("ai_{$model}_api_key") ?: config("ai.models.{$model}.api_key");
        if (empty($apiKey)) {
            throw new \InvalidArgumentException("Chave de API para o modelo {$model} não configurada.");
        }

        // Coletar termos de pesquisa
        $searchTerms = $this->collectSearchTerms($date, $limit, $filters);

        if ($searchTerms->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Nenhum termo de pesquisa encontrado com os critérios especificados.',
                'data' => null,
                'metrics' => null
            ];
        }

        // Coletar palavras-chave negativas e positivas para contexto
        $negativeKeywords = $this->collectNegativeKeywords();
        $positiveKeywords = $this->collectPositiveKeywords();

        // Obter instruções de IA (DB)
        $globalInstructions = setting('ai_global_custom_instructions', '');
        $modelSpecificInstructions = setting("ai_{$model}_custom_instructions", '');
        
        // Construir o prompt
        $prompt = $this->buildPrompt(
            $searchTerms,
            $negativeKeywords,
            $positiveKeywords,
            $globalInstructions,
            $modelSpecificInstructions,
            $date
        );

        // Obter nome exato do modelo (DB primeiro, .env fallback)
        $modelName = setting("ai_{$model}_model") ?: config("ai.models.{$model}.model_name", $model);

        // Chamar a API de IA e medir o tempo
        $startTime = microtime(true);
        
        try {
            $apiResponse = $this->callAiApi($model, $apiKey, $prompt);

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            // Preparar os resultados
            $results = $this->prepareResults($searchTerms, $apiResponse['results']);

            // Métricas da API
            $metrics = [
                'model' => $model,
                'model_name' => $modelName,
                'duration' => $duration,
                'prompt' => $prompt,
                'usage' => $apiResponse['usage'],
            ];

            return [
                'success' => true,
                'message' => 'Análise concluída com sucesso.',
                'data' => $results,
                'metrics' => $metrics
            ];
        } catch (\Exception $e) {
            Log::error("Erro na chamada à API de IA", [
                'model' => $model,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => "Erro ao chamar a API de IA: " . $e->getMessage(),
                'data' => null,
                'metrics' => null
            ];
        }
    }

    /**
     * Coleta termos de pesquisa com base nos critérios.
     *
     * @param Carbon|null $date Data específica (null para análise por custo)
     * @param int $limit Limite de termos
     * @param array $filters Filtros adicionais
     * @return Collection Coleção de termos de pesquisa
     */
    public function collectSearchTerms(?Carbon $date, int $limit, array $filters): Collection
    {
        $query = SearchTerm::query()->where('status', 'NONE');
        
        // Filtrar por data ou ordenar por custo
        if ($date !== null) {
            $query->whereDate('first_seen_at', $date);
        } else {
            $query->orderBy('cost_micros', 'desc');
        }
        
        // Aplicar filtros adicionais
        if (isset($filters['min_impressions']) && $filters['min_impressions'] > 0) {
            $query->where('impressions', '>=', $filters['min_impressions']);
        }
        
        if (isset($filters['min_clicks']) && $filters['min_clicks'] > 0) {
            $query->where('clicks', '>=', $filters['min_clicks']);
        }
        
        if (isset($filters['min_cost']) && $filters['min_cost'] > 0) {
            // Converter o custo de reais para micros (multiplicar por 1.000.000)
            $query->where('cost_micros', '>=', $filters['min_cost'] * 1000000);
        }
        
        return $query->limit($limit)->get();
    }

    /**
     * Coleta palavras-chave negativas existentes.
     *
     * @return Collection Coleção de palavras-chave negativas
     */
    public function collectNegativeKeywords(): Collection
    {
        return NegativeKeyword::all(['keyword', 'match_type', 'reason']);
    }

    /**
     * Coleta palavras-chave positivas existentes.
     *
     * @return Collection Coleção de palavras-chave positivas
     */
    public function collectPositiveKeywords(): Collection
    {
        return SearchTerm::where('status', 'ADDED')
            ->distinct()
            ->pluck('keyword_text')
            ->filter()
            ->values();
    }

    /**
     * Constrói o prompt para a IA.
     *
     * @param Collection $searchTerms Termos de pesquisa
     * @param Collection $negativeKeywords Palavras-chave negativas
     * @param Collection $positiveKeywords Palavras-chave positivas
     * @param string $globalInstructions Instruções globais
     * @param string $modelSpecificInstructions Instruções específicas do modelo
     * @param Carbon|null $date Data específica (null para análise por custo)
     * @return string Prompt formatado
     */
    public function buildPrompt(
        Collection $searchTerms,
        Collection $negativeKeywords,
        Collection $positiveKeywords,
        string $globalInstructions,
        string $modelSpecificInstructions,
        ?Carbon $date
    ): string {
        $prompt = "# Instruções\n\n";
        
        // Adicionar instruções customizadas
        if (!empty($globalInstructions)) {
            $prompt .= "{$globalInstructions}\n\n";
        }
        
        if (!empty($modelSpecificInstructions)) {
            $prompt .= "{$modelSpecificInstructions}\n\n";
        }
        
        // Adicionar instruções padrão se não houver instruções customizadas
        if (empty($globalInstructions) && empty($modelSpecificInstructions)) {
            $prompt .= "Você é um especialista em análise de palavras-chave para Google Ads. Sua tarefa é analisar termos de pesquisa e determinar se eles devem ser negativados (adicionados como palavras-chave negativas) com base no contexto fornecido.\n\n";
            $prompt .= "Para cada termo de pesquisa, forneça uma análise concisa e um racional para negativação ou manutenção. Considere o contexto das palavras-chave negativas existentes e seus motivos, bem como as palavras-chave positivas já adicionadas.\n\n";
            
            // Adicionar contexto específico com base no tipo de análise
            if ($date === null) {
                $prompt .= "Os termos de pesquisa fornecidos são os que geraram maior custo e ainda não foram adicionados como palavras-chave positivas ou negativas (status NONE). Dê atenção especial à relação entre custo e desempenho.\n\n";
            } else {
                $prompt .= "Os termos de pesquisa fornecidos são os que apareceram na data {$date->format('Y-m-d')} e ainda não foram adicionados como palavras-chave positivas ou negativas (status NONE).\n\n";
            }
        }
        
        // Adicionar contexto de palavras-chave negativas
        $prompt .= "# Palavras-chave Negativas Existentes\n\n";
        
        if ($negativeKeywords->isEmpty()) {
            $prompt .= "Não há palavras-chave negativas existentes.\n\n";
        } else {
            foreach ($negativeKeywords as $keyword) {
                $reason = !empty($keyword->reason) ? $keyword->reason : "Sem motivo especificado";
                $prompt .= "- Palavra-chave: \"{$keyword->keyword}\" (Tipo: {$keyword->match_type})\n";
                $prompt .= "  Motivo: {$reason}\n\n";
            }
        }
        
        // Adicionar contexto de palavras-chave positivas
        $prompt .= "# Palavras-chave Positivas Existentes\n\n";
        
        if ($positiveKeywords->isEmpty()) {
            $prompt .= "Não há palavras-chave positivas existentes.\n\n";
        } else {
            foreach ($positiveKeywords as $keyword) {
                $prompt .= "- \"{$keyword}\"\n";
            }
            $prompt .= "\n";
        }
        
        // Adicionar termos de pesquisa para análise
        $prompt .= "# Termos de Pesquisa para Análise";
        
        // Adicionar informação sobre ordenação se for análise por custo
        if ($date === null) {
            $prompt .= " (Ordenados por Custo)";
        }
        
        $prompt .= "\n\n";
        
        foreach ($searchTerms as $term) {
            $prompt .= "ID: {$term->id}\n";
            $prompt .= "Termo: \"{$term->search_term}\"\n";
            $prompt .= "Campanha: {$term->campaign_name}\n";
            $prompt .= "Grupo de Anúncios: {$term->ad_group_name}\n";
            $prompt .= "Impressões: {$term->impressions}\n";
            $prompt .= "Cliques: {$term->clicks}\n";
            $prompt .= "Custo: " . number_format($term->cost_micros / 1000000, 2, ',', '.') . " R$\n";
            $prompt .= "CTR: {$term->ctr}%\n\n";
        }
        
        // Adicionar instruções de formato de resposta
        $prompt .= "# Formato de Resposta\n\n";
        $prompt .= "Responda em formato JSON com a seguinte estrutura:\n\n";
        $prompt .= "```json\n";
        $prompt .= "[\n";
        $prompt .= "  {\n";
        $prompt .= "    \"term_id\": 123,\n";
        $prompt .= "    \"search_term\": \"texto do termo de pesquisa aqui\",\n";
        $prompt .= "    \"rationale\": \"Explicação concisa sobre por que o termo deve ser negativado ou mantido\",\n";
        $prompt .= "    \"should_negate\": true/false\n";
        $prompt .= "  },\n";
        $prompt .= "  ...\n";
        $prompt .= "]\n";
        $prompt .= "```\n\n";
        $prompt .= "Certifique-se de incluir todos os termos de pesquisa na resposta, ordenados por prioridade de negativação (os mais recomendados para negativar primeiro).\n";
        
        return $prompt;
    }

    /**
     * Chama a API de IA apropriada.
     *
     * @param string $model Nome do modelo
     * @param string $apiKey Chave da API
     * @param string $prompt Prompt para a IA
     * @return array Resposta processada da IA
     * @throws \Exception
     */
    private function callAiApi(string $model, string $apiKey, string $prompt): array
    {
        switch ($model) {
            case 'gemini':
                return $this->callGeminiApi($apiKey, $prompt);
            case 'openai':
                return $this->callOpenAiApi($apiKey, $prompt);
            case 'openrouter':
                return $this->callOpenRouterApi($apiKey, $prompt);
            default:
                throw new \Exception("Modelo não suportado: {$model}");
        }
    }

    /**
     * Chama a API Gemini.
     *
     * @param string $apiKey Chave da API
     * @param string $prompt Prompt para a IA
     * @return array Resposta processada
     * @throws \Exception
     */
    private function callGeminiApi(string $apiKey, string $prompt): array
    {
        $modelName = setting('ai_gemini_model') ?: config('ai.models.gemini.model_name', 'gemini-2.0-flash');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent";
        
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->timeout((int) setting('ai_api_timeout', 120))->withQueryParameters([
            'key' => $apiKey,
        ])->post($url, [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'topP' => 0.8,
                'topK' => 40,
            ]
        ]);
        
        if ($response->failed()) {
            throw new \Exception("Falha na chamada à API Gemini: " . $response->body());
        }
        
        $data = $response->json();
        
        // Extrair o texto da resposta
        if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            throw new \Exception("Formato de resposta da API Gemini inesperado: " . json_encode($data));
        }
        
        $responseText = $data['candidates'][0]['content']['parts'][0]['text'];
        
        // Extrair o JSON da resposta (pode estar dentro de blocos de código)
        preg_match('/```json\s*(.*?)\s*```/s', $responseText, $matches);
        
        if (isset($matches[1])) {
            $jsonText = $matches[1];
        } else {
            // Tentar encontrar qualquer array JSON na resposta
            preg_match('/\[\s*\{.*\}\s*\]/s', $responseText, $matches);
            if (isset($matches[0])) {
                $jsonText = $matches[0];
            } else {
                $jsonText = $responseText; // Usar o texto completo como fallback
            }
        }
        
        // Decodificar o JSON
        $result = json_decode($jsonText, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Erro ao decodificar JSON da resposta: " . json_last_error_msg() . "\nResposta: " . $responseText);
        }

        // Extrair token usage do Gemini
        $usage = null;
        if (isset($data['usageMetadata'])) {
            $usage = [
                'prompt_tokens' => $data['usageMetadata']['promptTokenCount'] ?? 0,
                'completion_tokens' => $data['usageMetadata']['candidatesTokenCount'] ?? 0,
                'total_tokens' => $data['usageMetadata']['totalTokenCount'] ?? 0,
            ];
        }

        return [
            'results' => $result,
            'usage' => $usage,
        ];
    }

    /**
     * Chama a API OpenAI.
     *
     * @param string $apiKey Chave da API
     * @param string $prompt Prompt para a IA
     * @return array Resposta processada
     * @throws \Exception
     */
    private function callOpenAiApi(string $apiKey, string $prompt): array
    {
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ])->timeout((int) setting('ai_api_timeout', 120))->post($url, [
            'model' => setting('ai_openai_model') ?: config('ai.models.openai.model_name', 'gpt-4o-mini'),
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Você é um especialista em análise de palavras-chave para Google Ads.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.2,
        ]);
        
        if ($response->failed()) {
            throw new \Exception("Falha na chamada à API OpenAI: " . $response->body());
        }
        
        $data = $response->json();
        
        // Extrair o texto da resposta
        if (!isset($data['choices'][0]['message']['content'])) {
            throw new \Exception("Formato de resposta da API OpenAI inesperado: " . json_encode($data));
        }
        
        $responseText = $data['choices'][0]['message']['content'];
        
        // Extrair o JSON da resposta (pode estar dentro de blocos de código)
        preg_match('/```json\s*(.*?)\s*```/s', $responseText, $matches);
        
        if (isset($matches[1])) {
            $jsonText = $matches[1];
        } else {
            // Tentar encontrar qualquer array JSON na resposta
            preg_match('/\[\s*\{.*\}\s*\]/s', $responseText, $matches);
            if (isset($matches[0])) {
                $jsonText = $matches[0];
            } else {
                $jsonText = $responseText; // Usar o texto completo como fallback
            }
        }
        
        // Decodificar o JSON
        $result = json_decode($jsonText, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Erro ao decodificar JSON da resposta: " . json_last_error_msg() . "\nResposta: " . $responseText);
        }

        // Extrair token usage do OpenAI
        $usage = null;
        if (isset($data['usage'])) {
            $usage = [
                'prompt_tokens' => $data['usage']['prompt_tokens'] ?? 0,
                'completion_tokens' => $data['usage']['completion_tokens'] ?? 0,
                'total_tokens' => $data['usage']['total_tokens'] ?? 0,
            ];
        }

        return [
            'results' => $result,
            'usage' => $usage,
        ];
    }

    /**
     * Chama a API OpenRouter.
     *
     * @param string $apiKey Chave da API
     * @param string $prompt Prompt para a IA
     * @return array Resposta processada
     * @throws \Exception
     */
    private function callOpenRouterApi(string $apiKey, string $prompt): array
    {
        $url = 'https://openrouter.ai/api/v1/chat/completions';

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
        ])->timeout((int) setting('ai_api_timeout', 120))->post($url, [
            'model' => setting('ai_openrouter_model') ?: config('ai.models.openrouter.model_name', 'google/gemini-2.0-flash-001'),
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Você é um especialista em análise de palavras-chave para Google Ads.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.2,
        ]);

        if ($response->failed()) {
            throw new \Exception("Falha na chamada à API OpenRouter: " . $response->body());
        }

        $data = $response->json();

        // Extrair o texto da resposta
        if (!isset($data['choices'][0]['message']['content'])) {
            throw new \Exception("Formato de resposta da API OpenRouter inesperado: " . json_encode($data));
        }

        $responseText = $data['choices'][0]['message']['content'];

        // Extrair o JSON da resposta (pode estar dentro de blocos de código)
        preg_match('/```json\s*(.*?)\s*```/s', $responseText, $matches);

        if (isset($matches[1])) {
            $jsonText = $matches[1];
        } else {
            // Tentar encontrar qualquer array JSON na resposta
            preg_match('/\[\s*\{.*\}\s*\]/s', $responseText, $matches);
            if (isset($matches[0])) {
                $jsonText = $matches[0];
            } else {
                $jsonText = $responseText; // Usar o texto completo como fallback
            }
        }

        // Decodificar o JSON
        $result = json_decode($jsonText, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Erro ao decodificar JSON da resposta: " . json_last_error_msg() . "\nResposta: " . $responseText);
        }

        // Extrair token usage do OpenRouter (mesmo formato do OpenAI)
        $usage = null;
        if (isset($data['usage'])) {
            $usage = [
                'prompt_tokens' => $data['usage']['prompt_tokens'] ?? 0,
                'completion_tokens' => $data['usage']['completion_tokens'] ?? 0,
                'total_tokens' => $data['usage']['total_tokens'] ?? 0,
            ];
        }

        return [
            'results' => $result,
            'usage' => $usage,
        ];
    }

    /**
     * Prepara os resultados para exibição.
     *
     * @param Collection $searchTerms Termos de pesquisa
     * @param array $response Resposta da IA
     * @return array Resultados formatados
     */
    private function prepareResults(Collection $searchTerms, array $response): array
    {
        // Mapear os resultados por ID do termo
        $resultsByTermId = [];
        foreach ($response as $result) {
            if (isset($result['term_id'])) {
                $resultsByTermId[$result['term_id']] = $result;
            }
        }
        
        // Preparar os dados para a tabela
        $tableData = [];
        foreach ($searchTerms as $term) {
            $result = $resultsByTermId[$term->id] ?? null;
            
            if ($result) {
                $shouldNegate = $result['should_negate'] ?? false;
                $rationale = $result['rationale'] ?? 'Sem análise disponível';
                
                $tableData[] = [
                    'id' => $term->id,
                    'search_term' => $term->search_term,
                    'impressions' => $term->impressions,
                    'clicks' => $term->clicks,
                    'cost_micros' => $term->cost_micros,
                    'cost_formatted' => 'R$ ' . number_format($term->cost_micros / 1000000, 2, ',', '.'),
                    'ctr' => $term->ctr,
                    'campaign_name' => $term->campaign_name,
                    'ad_group_name' => $term->ad_group_name,
                    'should_negate' => $shouldNegate,
                    'rationale' => $rationale
                ];
            }
        }
        
        // Ordenar por recomendação de negativação (SIM primeiro)
        usort($tableData, function ($a, $b) {
            if ($a['should_negate'] === $b['should_negate']) {
                return 0;
            }
            return $a['should_negate'] ? -1 : 1;
        });
        
        return $tableData;
    }

    /**
     * Analisa termos específicos para sugestão de negativação.
     *
     * @param string $model Nome do modelo de IA
     * @param array $terms Array de termos para análise
     * @return array Resultados da análise
     */
    public function analyzeTermsForNegation(string $model, array $terms): array
    {
        $apiKey = setting("ai_{$model}_api_key") ?: config("ai.models.{$model}.api_key");
        if (empty($apiKey)) {
            throw new \InvalidArgumentException("Chave de API para o modelo {$model} não configurada.");
        }

        // Construir prompt simplificado para análise de negativação
        $prompt = "Você é um especialista em Google Ads. Analise os seguintes termos de pesquisa e sugira quais devem ser negativados.\n\n";
        $prompt .= "Critérios para negativação:\n";
        $prompt .= "- Termos irrelevantes para o negócio\n";
        $prompt .= "- Termos de concorrência que não convertem\n";
        $prompt .= "- Termos muito genéricos que gastam sem retorno\n";
        $prompt .= "- Termos de pesquisa informacional (não transacional)\n\n";
        $prompt .= "Termos para análise:\n";

        foreach ($terms as $term) {
            $prompt .= "- ID: {$term['id']}, Termo: \"{$term['search_term']}\", Impressões: {$term['impressions']}, Cliques: {$term['clicks']}, Custo: R$ " . number_format($term['cost_micros'] / 1000000, 2) . "\n";
        }

        $prompt .= "\nResponda em formato JSON com a seguinte estrutura:\n";
        $prompt .= "{\n";
        $prompt .= "  \"suggestions\": [\n";
        $prompt .= "    {\n";
        $prompt .= "      \"term_id\": 123,\n";
        $prompt .= "      \"search_term\": \"texto do termo\",\n";
        $prompt .= "      \"should_negate\": true/false,\n";
        $prompt .= "      \"rationale\": \"explicação curta\",\n";
        $prompt .= "      \"priority\": \"high/medium/low\"\n";
        $prompt .= "    }\n";
        $prompt .= "  ],\n";
        $prompt .= "  \"summary\": \"Resumo geral da análise\"\n";
        $prompt .= "}\n";

        $apiResponse = $this->callAiApi($model, $apiKey, $prompt);
        $response = $apiResponse['results'];

        return [
            'suggestions' => $response['suggestions'] ?? [],
            'summary' => $response['summary'] ?? 'Análise concluída',
            'terms_count' => count($terms),
        ];
    }
}
