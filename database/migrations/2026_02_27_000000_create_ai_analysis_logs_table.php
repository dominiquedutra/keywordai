<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_analysis_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source', 20);
            $table->string('analysis_type', 10);
            $table->date('date_filter')->nullable();
            $table->json('filters');
            $table->json('settings_snapshot');
            $table->string('model', 30);
            $table->string('model_name', 100);
            $table->integer('term_limit');
            $table->integer('terms_found');
            $table->longText('prompt');
            $table->integer('prompt_size');
            $table->smallInteger('reply_code')->default(0);
            $table->longText('reply')->nullable();
            $table->integer('prompt_tokens')->nullable();
            $table->integer('completion_tokens')->nullable();
            $table->integer('total_tokens')->nullable();
            $table->decimal('duration', 8, 2)->nullable();
            $table->boolean('success')->default(false);
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
            $table->index('user_id');
            $table->index('model');
            $table->index('success');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_analysis_logs');
    }
};
