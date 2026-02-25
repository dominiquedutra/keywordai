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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Usuário que realizou a ação
            $table->string('action_type'); // add_keyword, add_negative_keyword, etc.
            $table->string('entity_type'); // keyword, negative_keyword, etc.
            $table->unsignedBigInteger('entity_id')->nullable(); // ID da entidade afetada
            $table->unsignedBigInteger('ad_group_id')->nullable(); // ID do grupo de anúncios (quando aplicável)
            $table->string('ad_group_name')->nullable(); // Nome do grupo de anúncios (quando aplicável)
            $table->unsignedBigInteger('campaign_id')->nullable(); // ID da campanha (quando aplicável)
            $table->string('campaign_name')->nullable(); // Nome da campanha (quando aplicável)
            $table->json('details')->nullable(); // Detalhes adicionais da ação (termo, match_type, etc.)
            $table->timestamp('created_at')->useCurrent(); // Apenas created_at, sem updated_at
            
            // Chave estrangeira
            $table->foreign('user_id')->references('id')->on('users');
            
            // Índices para otimização de consultas
            $table->index('user_id');
            $table->index('action_type');
            $table->index('entity_type');
            $table->index('entity_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
