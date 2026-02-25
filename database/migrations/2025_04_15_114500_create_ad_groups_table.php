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
        Schema::create('ad_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('google_ad_group_id')->unique();
            $table->string('resource_name');
            $table->string('name');
            $table->string('status');
            $table->unsignedBigInteger('campaign_id');
            $table->timestamps();
            
            // Chave estrangeira para a tabela campaigns
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
            
            // Índices para otimização de consultas
            $table->index('google_ad_group_id');
            $table->index('status');
            $table->index('name');
            $table->index('campaign_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_groups');
    }
};
