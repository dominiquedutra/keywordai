<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Execute o seeder.
     */
    public function run(): void
    {
        // Configurações para match type padrão
        Setting::setValue(
            'default_keyword_match_type', 
            'phrase', 
            'string',
            'Tipo de correspondência padrão para adição de palavras-chave positivas'
        );
        
        Setting::setValue(
            'default_negative_keyword_match_type', 
            'phrase', 
            'string',
            'Tipo de correspondência padrão para adição de palavras-chave negativas'
        );
        
        // Configurações para IA
        Setting::setValue(
            'ai_global_custom_instructions', 
            '', 
            'text',
            'Instruções globais customizadas para todos os modelos de IA'
        );
        
        Setting::setValue(
            'ai_gemini_custom_instructions', 
            '', 
            'text',
            'Instruções customizadas específicas para o modelo Gemini'
        );
        
        Setting::setValue(
            'ai_openai_custom_instructions', 
            '', 
            'text',
            'Instruções customizadas específicas para o modelo OpenAI'
        );
        
        Setting::setValue(
            'ai_openrouter_custom_instructions',
            '',
            'text',
            'Instruções customizadas específicas para o modelo OpenRouter'
        );
    }
}
