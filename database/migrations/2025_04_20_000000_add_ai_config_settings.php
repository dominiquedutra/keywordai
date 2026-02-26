<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $settings = [
        [
            'key' => 'ai_default_model',
            'value' => 'gemini',
            'type' => 'string',
            'description' => 'Modelo de IA padrão (gemini, openai, perplexity)',
        ],
        [
            'key' => 'ai_gemini_api_key',
            'value' => '',
            'type' => 'encrypted',
            'description' => 'Chave de API do Google Gemini',
        ],
        [
            'key' => 'ai_gemini_model',
            'value' => 'gemini-2.0-flash',
            'type' => 'string',
            'description' => 'Nome do modelo Gemini',
        ],
        [
            'key' => 'ai_openai_api_key',
            'value' => '',
            'type' => 'encrypted',
            'description' => 'Chave de API da OpenAI',
        ],
        [
            'key' => 'ai_openai_model',
            'value' => 'gpt-4o-mini',
            'type' => 'string',
            'description' => 'Nome do modelo OpenAI',
        ],
        [
            'key' => 'ai_perplexity_api_key',
            'value' => '',
            'type' => 'encrypted',
            'description' => 'Chave de API da Perplexity',
        ],
        [
            'key' => 'ai_perplexity_model',
            'value' => 'sonar-pro',
            'type' => 'string',
            'description' => 'Nome do modelo Perplexity',
        ],
    ];

    public function up(): void
    {
        foreach ($this->settings as $setting) {
            $exists = DB::table('settings')->where('key', $setting['key'])->exists();

            if (!$exists) {
                DB::table('settings')->insert(array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    public function down(): void
    {
        $keys = array_column($this->settings, 'key');
        DB::table('settings')->whereIn('key', $keys)->delete();
    }
};
