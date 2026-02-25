<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Lista de chaves de configuração de IA a serem removidas.
     *
     * @var array
     */
    protected $aiSettings = [
        'ai_gemini_api_key',
        'ai_openai_api_key',
        'ai_perplexity_api_key',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remover as configurações de IA da tabela settings
        foreach ($this->aiSettings as $key) {
            DB::table('settings')->where('key', $key)->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não é possível restaurar os valores exatos, então apenas criamos registros vazios
        foreach ($this->aiSettings as $key) {
            // Verificar se a configuração já existe
            $exists = DB::table('settings')->where('key', $key)->exists();
            
            if (!$exists) {
                DB::table('settings')->insert([
                    'key' => $key,
                    'value' => '',
                    'type' => 'string',
                    'description' => 'Configuração de IA (restaurada)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
