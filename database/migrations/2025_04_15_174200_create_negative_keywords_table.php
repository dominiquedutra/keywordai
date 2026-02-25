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
        Schema::create('negative_keywords', function (Blueprint $table) {
            $table->id();
            $table->string('keyword');
            $table->string('match_type'); // broad, phrase ou exact
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('list_id'); // ID da lista de palavras-chave negativas no Google Ads
            $table->string('resource_name')->nullable(); // Resource name no Google Ads
            $table->unsignedBigInteger('created_by_id'); // Referência ao usuário que criou
            $table->unsignedBigInteger('updated_by_id')->nullable(); // Referência ao usuário que atualizou
            $table->timestamps();
            
            // Chaves estrangeiras
            $table->foreign('created_by_id')->references('id')->on('users');
            $table->foreign('updated_by_id')->references('id')->on('users');
            
            // Índices para otimização de consultas
            $table->index('keyword');
            $table->index('match_type');
            $table->index('list_id');
            $table->index('created_by_id');
            
            // Constraint única para evitar duplicatas
            $table->unique(['keyword', 'match_type', 'list_id'], 'negative_keyword_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('negative_keywords');
    }
};
