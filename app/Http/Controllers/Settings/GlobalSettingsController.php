<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class GlobalSettingsController extends Controller
{
    /**
     * Exibir a página de configurações globais.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $settings = Setting::all();
        
        return view('settings.global', [
            'settings' => $settings
        ]);
    }
    
    /**
     * Atualizar as configurações globais.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
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

        // API keys: only update if non-empty (keeps existing key when submitted empty)
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

        return redirect()->route('settings.global.index')
            ->with('success', 'Configurações atualizadas com sucesso!');
    }
}
