<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('search_terms', function (Blueprint $table) {
            // Adicionar campos para armazenar o texto da keyword e o tipo de correspondência
            $table->string('keyword_text')->nullable()->after('search_term');
            $table->string('match_type')->nullable()->after('keyword_text');
            
            // Adicionar índice para otimização de consultas
            $table->index('keyword_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('search_terms', function (Blueprint $table) {
            // Remover índice
            $table->dropIndex(['keyword_text']);
            
            // Remover colunas
            $table->dropColumn(['keyword_text', 'match_type']);
        });
    }
};
