<?php

namespace App\Services;

use App\Models\NegativeKeyword;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NegativeKeywordsSummaryService
{
    /**
     * Generate an AI-synthesized summary of all negative keywords.
     *
     * @param string|null $model Provider override (gemini, openai, openrouter)
     * @return array{success: bool, summary: ?string, meta: ?array, error: ?string}
     */
    public function generate(?string $model = null): array
    {
        $model = $model ?: (setting('ai_summary_model', 'gemini'));
        $modelName = setting('ai_summary_model_name', 'gemini-2.5-pro');
        $apiKey = setting("ai_{$model}_api_key") ?: config("ai.models.{$model}.api_key");

        if (empty($apiKey)) {
            return ['success' => false, 'summary' => null, 'meta' => null, 'error' => "API key for {$model} not configured."];
        }

        $keywords = NegativeKeyword::all(['keyword', 'match_type', 'reason']);

        if ($keywords->isEmpty()) {
            return ['success' => false, 'summary' => null, 'meta' => null, 'error' => 'No negative keywords found.'];
        }

        $prompt = $this->buildMetaPrompt($keywords);

        try {
            $startTime = microtime(true);
            $result = $this->callAi($model, $modelName, $apiKey, $prompt);
            $duration = round(microtime(true) - $startTime, 2);

            $summary = $result['text'];
            $meta = [
                'generated_at' => now()->toIso8601String(),
                'keyword_count' => $keywords->count(),
                'model_used' => "{$model}/{$modelName}",
                'summary_size_bytes' => strlen($summary),
                'duration_seconds' => $duration,
                'prompt_tokens' => $result['usage']['prompt_tokens'] ?? null,
                'completion_tokens' => $result['usage']['completion_tokens'] ?? null,
            ];

            Setting::setValue('ai_negatives_summary', $summary, 'text');
            Setting::setValue('ai_negatives_summary_meta', json_encode($meta), 'json');
            Setting::setValue('ai_negatives_summary_stale', '0', 'boolean');

            return ['success' => true, 'summary' => $summary, 'meta' => $meta, 'error' => null];
        } catch (\Exception $e) {
            Log::error('Failed to generate negative keywords summary', [
                'error' => $e->getMessage(),
                'model' => $model,
            ]);

            return ['success' => false, 'summary' => null, 'meta' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get the stored AI-generated summary.
     */
    public function getSummary(): ?string
    {
        return setting('ai_negatives_summary');
    }

    /**
     * Get the stored metadata for the summary.
     */
    public function getMeta(): ?array
    {
        $meta = setting('ai_negatives_summary_meta');
        if (empty($meta)) {
            return null;
        }

        return is_array($meta) ? $meta : json_decode($meta, true);
    }

    /**
     * Build a compact keyword list (no reasons) for prompt injection.
     * Format: "keyword (match_type)\n" per line.
     */
    public function getCompactKeywordList(): string
    {
        $keywords = NegativeKeyword::all(['keyword', 'match_type']);

        if ($keywords->isEmpty()) {
            return '';
        }

        return $keywords->map(fn ($kw) => "{$kw->keyword} ({$kw->match_type})")->implode("\n");
    }

    /**
     * Check if the summary needs regeneration.
     */
    public function isStale(): bool
    {
        return (bool) setting('ai_negatives_summary_stale', true);
    }

    /**
     * Mark the summary as stale (needs regeneration).
     */
    public function markStale(): void
    {
        Setting::setValue('ai_negatives_summary_stale', '1', 'boolean');
    }

    /**
     * Build the meta-prompt that instructs AI to synthesize keyword patterns.
     */
    private function buildMetaPrompt($keywords): string
    {
        $keywordBlock = $keywords->map(function ($kw) {
            $reason = !empty($kw->reason) ? $kw->reason : 'Sem motivo';
            return "- \"{$kw->keyword}\" ({$kw->match_type}) — {$reason}";
        })->implode("\n");

        return <<<PROMPT
# Tarefa: Gerar Perfil de Negativação

Você receberá uma lista de {$keywords->count()} palavras-chave negativas de uma conta Google Ads, cada uma com tipo de correspondência e motivo de negativação. Sua tarefa é sintetizar essas palavras-chave em um "Perfil de Negativação" compacto e estruturado.

## Objetivo
Criar um documento que capture os PADRÕES e a LÓGICA por trás das negativações, de forma que um modelo de IA futuro consiga entender a estratégia de negativação sem precisar ver cada keyword individual com seu motivo completo.

## Formato de Saída Esperado

Produza o perfil em texto corrido e estruturado (NÃO JSON), seguindo esta estrutura:

### 1. Visão Geral da Estratégia (2-3 frases)
Descreva o perfil geral do anunciante e a lógica macro de negativação.

### 2. Categorias de Negativação
Para cada categoria identificada, forneça:
- **Nome da Categoria**: título descritivo
- **Padrão**: que tipo de termos são negativados nesta categoria
- **Lógica**: por que esses termos são negativados
- **Tipo de Correspondência Preferido**: broad/phrase/exact e por quê
- **Exemplos**: 3-5 keywords representativas

### 3. Padrões Linguísticos
- Radicais/prefixos comuns negativados
- Padrões de composição de termos (ex: "termo + localidade", "como + verbo")

### 4. Distribuição de Tipos de Correspondência
- Porcentagem aproximada de broad/phrase/exact
- Quando cada tipo é preferido

### 5. Regras Implícitas
Regras que você inferiu a partir do conjunto de keywords (ex: "todo termo com 'grátis' é negativado", "termos de concorrentes são negativados em phrase").

## Restrições
- Escreva em português brasileiro
- Seja conciso mas completo (alvo: 2000-4000 palavras)
- Foque em padrões reutilizáveis, não em listar cada keyword
- O documento será usado para informar futuras decisões de negativação por IA

## Palavras-chave Negativas ({$keywords->count()} total)

{$keywordBlock}
PROMPT;
    }

    /**
     * Call the AI provider to generate the summary.
     *
     * @return array{text: string, usage: array}
     */
    private function callAi(string $model, string $modelName, string $apiKey, string $prompt): array
    {
        $timeout = (int) setting('ai_api_timeout', 120);

        return match ($model) {
            'gemini' => $this->callGemini($apiKey, $modelName, $prompt, $timeout),
            'openai' => $this->callOpenAi($apiKey, $modelName, $prompt, $timeout),
            'openrouter' => $this->callOpenRouter($apiKey, $modelName, $prompt, $timeout),
            default => throw new \InvalidArgumentException("Unsupported model: {$model}"),
        };
    }

    private function callGemini(string $apiKey, string $modelName, string $prompt, int $timeout): array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent";

        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->timeout($timeout)
            ->withQueryParameters(['key' => $apiKey])
            ->post($url, [
                'contents' => [['parts' => [['text' => $prompt]]]],
                'generationConfig' => [
                    'temperature' => 0.3,
                    'maxOutputTokens' => 8192,
                ],
            ]);

        if ($response->failed()) {
            throw new \Exception("Gemini API failed: " . $response->body());
        }

        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text']
            ?? throw new \Exception("Unexpected Gemini response format");

        $usage = [
            'prompt_tokens' => $data['usageMetadata']['promptTokenCount'] ?? 0,
            'completion_tokens' => $data['usageMetadata']['candidatesTokenCount'] ?? 0,
        ];

        return ['text' => $text, 'usage' => $usage];
    }

    private function callOpenAi(string $apiKey, string $modelName, string $prompt, int $timeout): array
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$apiKey}",
        ])->timeout($timeout)->post('https://api.openai.com/v1/chat/completions', [
            'model' => $modelName,
            'messages' => [
                ['role' => 'system', 'content' => 'Você é um especialista em Google Ads e análise de palavras-chave negativas.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.3,
            'max_tokens' => 8192,
        ]);

        if ($response->failed()) {
            throw new \Exception("OpenAI API failed: " . $response->body());
        }

        $data = $response->json();
        $text = $data['choices'][0]['message']['content']
            ?? throw new \Exception("Unexpected OpenAI response format");

        $usage = [
            'prompt_tokens' => $data['usage']['prompt_tokens'] ?? 0,
            'completion_tokens' => $data['usage']['completion_tokens'] ?? 0,
        ];

        return ['text' => $text, 'usage' => $usage];
    }

    private function callOpenRouter(string $apiKey, string $modelName, string $prompt, int $timeout): array
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$apiKey}",
        ])->timeout($timeout)->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => $modelName,
            'messages' => [
                ['role' => 'system', 'content' => 'Você é um especialista em Google Ads e análise de palavras-chave negativas.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.3,
            'max_tokens' => 8192,
        ]);

        if ($response->failed()) {
            throw new \Exception("OpenRouter API failed: " . $response->body());
        }

        $data = $response->json();
        $text = $data['choices'][0]['message']['content']
            ?? throw new \Exception("Unexpected OpenRouter response format");

        $usage = [
            'prompt_tokens' => $data['usage']['prompt_tokens'] ?? 0,
            'completion_tokens' => $data['usage']['completion_tokens'] ?? 0,
        ];

        return ['text' => $text, 'usage' => $usage];
    }
}
