<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
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
                'has_gemini_key' => !empty(Setting::getValue('ai_gemini_api_key')),
                'has_openai_key' => !empty(Setting::getValue('ai_openai_api_key')),
                'has_openrouter_key' => !empty(Setting::getValue('ai_openrouter_api_key')),
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
}
