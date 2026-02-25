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
            'ai_global_custom_instructions' => ['nullable', 'string'],
            'ai_gemini_custom_instructions' => ['nullable', 'string'],
            'ai_openai_custom_instructions' => ['nullable', 'string'],
            'ai_perplexity_custom_instructions' => ['nullable', 'string'],
        ]);
        
        foreach ($validated as $key => $value) {
            // Determinar o tipo de configuração
            $type = 'string';
            if (in_array($key, ['ai_global_custom_instructions', 'ai_gemini_custom_instructions', 'ai_openai_custom_instructions', 'ai_perplexity_custom_instructions'])) {
                $type = 'text';
            }
            
            Setting::setValue($key, $value, $type);
        }
        
        return redirect()->route('settings.global.index')
            ->with('success', 'Configurações atualizadas com sucesso!');
    }
}
