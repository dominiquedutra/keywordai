<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\NegativeKeywordsSummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Inertia\Response;

class GlobalSettingsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('settings/Global', [
            'settings' => [
                'default_keyword_match_type' => Setting::getValue('default_keyword_match_type', 'phrase'),
                'default_negative_keyword_match_type' => Setting::getValue('default_negative_keyword_match_type', 'phrase'),
                'ai_default_model' => Setting::getValue('ai_default_model', 'gemini'),
                'ai_gemini_model' => Setting::getValue('ai_gemini_model', 'gemini-2.0-flash'),
                'ai_openai_model' => Setting::getValue('ai_openai_model', 'gpt-4o-mini'),
                'ai_openrouter_model' => Setting::getValue('ai_openrouter_model', 'google/gemini-2.0-flash-001'),
                'ai_global_custom_instructions' => Setting::getValue('ai_global_custom_instructions', ''),
                'ai_gemini_custom_instructions' => Setting::getValue('ai_gemini_custom_instructions', ''),
                'ai_openai_custom_instructions' => Setting::getValue('ai_openai_custom_instructions', ''),
                'ai_openrouter_custom_instructions' => Setting::getValue('ai_openrouter_custom_instructions', ''),
                'ai_api_timeout' => Setting::getValue('ai_api_timeout', '120'),
                'has_gemini_key' => !empty(Setting::getValue('ai_gemini_api_key')),
                'has_openai_key' => !empty(Setting::getValue('ai_openai_api_key')),
                'has_openrouter_key' => !empty(Setting::getValue('ai_openrouter_api_key')),
                'ai_summary_model' => Setting::getValue('ai_summary_model', 'gemini'),
                'ai_summary_model_name' => Setting::getValue('ai_summary_model_name', 'gemini-2.5-pro'),
                'ai_gemini_max_output_tokens' => Setting::getValue('ai_gemini_max_output_tokens', '8192'),
                'negatives_summary' => Setting::getValue('ai_negatives_summary', ''),
                'negatives_summary_meta' => Setting::getValue('ai_negatives_summary_meta'),
                'negatives_summary_stale' => (bool) Setting::getValue('ai_negatives_summary_stale', true),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'default_keyword_match_type' => ['required', 'string', 'in:exact,phrase,broad'],
            'default_negative_keyword_match_type' => ['required', 'string', 'in:exact,phrase,broad'],
            'ai_default_model' => ['required', 'string', 'in:gemini,openai,openrouter'],
            'ai_gemini_api_key' => ['nullable', 'string'],
            'ai_gemini_model' => ['required', 'string', 'max:100'],
            'ai_openai_api_key' => ['nullable', 'string'],
            'ai_openai_model' => ['required', 'string', 'max:100'],
            'ai_openrouter_api_key' => ['nullable', 'string'],
            'ai_openrouter_model' => ['required', 'string', 'max:100'],
            'ai_global_custom_instructions' => ['nullable', 'string'],
            'ai_gemini_custom_instructions' => ['nullable', 'string'],
            'ai_openai_custom_instructions' => ['nullable', 'string'],
            'ai_openrouter_custom_instructions' => ['nullable', 'string'],
            'ai_api_timeout' => ['required', 'integer', 'min:10', 'max:300'],
            'ai_summary_model' => ['nullable', 'string', 'in:gemini,openai,openrouter'],
            'ai_summary_model_name' => ['nullable', 'string', 'max:100'],
            'ai_gemini_max_output_tokens' => ['nullable', 'integer', 'min:1024', 'max:65536'],
        ]);

        $encryptedKeys = ['ai_gemini_api_key', 'ai_openai_api_key', 'ai_openrouter_api_key'];
        $textFields = ['ai_global_custom_instructions', 'ai_gemini_custom_instructions', 'ai_openai_custom_instructions', 'ai_openrouter_custom_instructions'];

        foreach ($validated as $key => $value) {
            if (in_array($key, $encryptedKeys)) {
                if (!empty($value)) {
                    Setting::setValue($key, $value, 'encrypted');
                }
                continue;
            }

            $type = 'string';
            if (in_array($key, $textFields)) {
                $type = 'text';
            }

            Setting::setValue($key, $value, $type);
        }

        return redirect()->route('settings.global.index');
    }

    public function regenerateNegativesSummary(NegativeKeywordsSummaryService $summaryService): JsonResponse
    {
        $result = $summaryService->generate();

        if (!$result['success']) {
            return response()->json(['error' => $result['error']], 422);
        }

        return response()->json([
            'summary' => $result['summary'],
            'meta' => $result['meta'],
        ]);
    }

    public function fetchOpenRouterModels(): JsonResponse
    {
        $apiKey = Setting::getValue('ai_openrouter_api_key');

        if (empty($apiKey)) {
            return response()->json(['error' => 'Configure an OpenRouter API key first.'], 422);
        }

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
        ])->timeout(15)->get('https://openrouter.ai/api/v1/models');

        if (! $response->successful()) {
            return response()->json(['error' => 'Failed to fetch models from OpenRouter.'], 502);
        }

        $data = $response->json('data', []);

        $models = collect($data)
            ->filter(fn ($m) => isset($m['id'], $m['pricing']['prompt'], $m['pricing']['completion'])
                && $m['pricing']['prompt'] !== '0'
                && str_contains($m['architecture']['modality'] ?? '', 'text'))
            ->map(function ($m) {
                $inputPerMillion = round((float) $m['pricing']['prompt'] * 1_000_000, 2);
                $outputPerMillion = round((float) $m['pricing']['completion'] * 1_000_000, 2);

                return [
                    'id' => $m['id'],
                    'name' => $m['name'] ?? $m['id'],
                    'inputPrice' => number_format($inputPerMillion, 2),
                    'outputPrice' => number_format($outputPerMillion, 2),
                    'badge' => $this->classifyModelBadge($inputPerMillion),
                    'badgeColor' => $this->classifyModelBadgeColor($inputPerMillion),
                ];
            })
            ->sortBy('inputPrice')
            ->values()
            ->take(50);

        return response()->json(['models' => $models]);
    }

    private function classifyModelBadge(float $inputPricePerMillion): string
    {
        if ($inputPricePerMillion <= 0.20) {
            return 'Cheapest';
        }
        if ($inputPricePerMillion <= 1.00) {
            return '⚡ Fast';
        }

        return 'Precise';
    }

    private function classifyModelBadgeColor(float $inputPricePerMillion): string
    {
        if ($inputPricePerMillion <= 0.20) {
            return 'green';
        }
        if ($inputPricePerMillion <= 1.00) {
            return 'blue';
        }

        return 'purple';
    }
}
