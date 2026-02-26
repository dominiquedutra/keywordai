<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remove Perplexity settings
        DB::table('settings')->whereIn('key', [
            'ai_perplexity_api_key',
            'ai_perplexity_model',
        ])->delete();

        // Rename custom instructions key
        DB::table('settings')
            ->where('key', 'ai_perplexity_custom_instructions')
            ->update([
                'key' => 'ai_openrouter_custom_instructions',
                'description' => 'Instruções customizadas específicas para o modelo OpenRouter',
                'updated_at' => now(),
            ]);

        // Insert OpenRouter settings
        $openrouterSettings = [
            [
                'key' => 'ai_openrouter_api_key',
                'value' => '',
                'type' => 'encrypted',
                'description' => 'Chave de API do OpenRouter',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'ai_openrouter_model',
                'value' => 'google/gemini-2.0-flash-001',
                'type' => 'string',
                'description' => 'Nome do modelo OpenRouter',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($openrouterSettings as $setting) {
            if (!DB::table('settings')->where('key', $setting['key'])->exists()) {
                DB::table('settings')->insert($setting);
            }
        }

        // Update ai_default_model description
        DB::table('settings')
            ->where('key', 'ai_default_model')
            ->update([
                'description' => 'Modelo de IA padrão (gemini, openai, openrouter)',
                'updated_at' => now(),
            ]);

        // If current default is perplexity, switch to gemini
        DB::table('settings')
            ->where('key', 'ai_default_model')
            ->where('value', 'perplexity')
            ->update([
                'value' => 'gemini',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Remove OpenRouter settings
        DB::table('settings')->whereIn('key', [
            'ai_openrouter_api_key',
            'ai_openrouter_model',
        ])->delete();

        // Rename custom instructions back
        DB::table('settings')
            ->where('key', 'ai_openrouter_custom_instructions')
            ->update([
                'key' => 'ai_perplexity_custom_instructions',
                'description' => 'Instruções customizadas específicas para o modelo Perplexity',
                'updated_at' => now(),
            ]);

        // Re-insert Perplexity settings
        $perplexitySettings = [
            [
                'key' => 'ai_perplexity_api_key',
                'value' => '',
                'type' => 'encrypted',
                'description' => 'Chave de API da Perplexity',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'ai_perplexity_model',
                'value' => 'sonar-pro',
                'type' => 'string',
                'description' => 'Nome do modelo Perplexity',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($perplexitySettings as $setting) {
            if (!DB::table('settings')->where('key', $setting['key'])->exists()) {
                DB::table('settings')->insert($setting);
            }
        }

        // Restore ai_default_model description
        DB::table('settings')
            ->where('key', 'ai_default_model')
            ->update([
                'description' => 'Modelo de IA padrão (gemini, openai, perplexity)',
                'updated_at' => now(),
            ]);
    }
};
