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
        Schema::create('search_terms', function (Blueprint $table) {
            $table->id();
            $table->string('search_term');
            $table->unsignedInteger('impressions')->default(0);
            $table->unsignedInteger('clicks')->default(0);
            $table->unsignedBigInteger('cost_micros')->default(0);
            $table->float('ctr', 8, 4)->default(0); // Click-Through Rate em porcentagem
            $table->string('status')->nullable(); // NONE, ADDED, EXCLUDED, ADDED_EXCLUDED
            
            // Informações da campanha
            $table->unsignedBigInteger('campaign_id');
            $table->string('campaign_name');
            
            // Informações do grupo de anúncios
            $table->unsignedBigInteger('ad_group_id');
            $table->string('ad_group_name');
            
            // Data em que o termo foi visto pela primeira vez
            $table->date('first_seen_at');
            
            // Timestamps padrão do Laravel
            $table->timestamps();
            
            // Constraint única para campanha + grupo de anúncios + termo de pesquisa
            $table->unique(['campaign_id', 'ad_group_id', 'search_term'], 'search_term_unique');
            
            // Índices para otimização de consultas
            $table->index('search_term');
            $table->index('status');
            $table->index('first_seen_at');
            $table->index('campaign_id');
            $table->index('ad_group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_terms');
    }
};
