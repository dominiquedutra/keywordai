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
        Schema::create('search_term_sync_dates', function (Blueprint $table) {
            $table->id();
            $table->date('sync_date')->unique()->comment('A data específica que foi (ou será) sincronizada');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending')->comment('Status da sincronização');
            $table->string('job_id')->nullable()->comment('ID do job na fila (se aplicável)');
            $table->integer('attempts')->default(0)->comment('Número de tentativas');
            $table->text('last_error')->nullable()->comment('Mensagem do último erro');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_term_sync_dates');
    }
};
