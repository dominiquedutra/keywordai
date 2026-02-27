<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocsController;
use App\Http\Controllers\KeywordController;
use App\Http\Controllers\NegativeKeywordController;
use App\Http\Controllers\SearchTermController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Rotas públicas
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return Inertia::render('Welcome');
})->name('home');

// Public API Documentation
Route::get('/api/docs', [App\Http\Controllers\Api\ApiDocsController::class, 'index']);

// Public Sistema Documentation
Route::get('/docs/sistema', [DocsController::class, 'sistemaIndex'])->name('docs.sistema.index');
Route::get('/docs/sistema/batch-stats-sync', [DocsController::class, 'batchStatsSync'])->name('docs.sistema.batch-stats-sync');

// Incluir rotas de autenticação
require __DIR__.'/auth.php';

// Rotas protegidas (requer autenticação)
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Rotas para palavras-chave negativas
    Route::get('/negative-keywords', [NegativeKeywordController::class, 'index'])->name('negative-keywords.index');
    Route::get('/negative-keywords/{negativeKeyword}', [NegativeKeywordController::class, 'show'])->name('negative-keywords.show');
    Route::get('/negative-keyword/add', [NegativeKeywordController::class, 'create'])->name('negative-keyword.create');
    Route::post('/negative-keyword/add', [NegativeKeywordController::class, 'store'])->name('negative-keyword.store');
    Route::get('/negative-keyword/success', [NegativeKeywordController::class, 'success'])->name('negative-keyword.success');

    // Rotas para adicionar palavras-chave positivas
    Route::get('/keyword/add', [KeywordController::class, 'create'])->name('keyword.add.create');
    Route::post('/keyword/add', [KeywordController::class, 'store'])->name('keyword.add.store');
    Route::get('/keyword/add/success', [KeywordController::class, 'success'])->name('keyword.add.success');

    // Rotas para termos de pesquisa
    Route::get('/search-terms', [SearchTermController::class, 'index'])->name('search-terms.index');
    Route::post('/search-terms/{search_term}/refresh', [SearchTermController::class, 'refreshStats'])->name('search-terms.refresh');

    // Rotas para logs de atividade
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/activity-logs/{activity_log}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
    
    // Rotas para análise de IA
    Route::get('/ai-analysis', [App\Http\Controllers\AiAnalysisController::class, 'index'])->name('ai-analysis.index');
    Route::post('/ai-analysis/preview', [App\Http\Controllers\AiAnalysisController::class, 'preview'])->name('ai-analysis.preview');
    Route::post('/ai-analysis/analyze', [App\Http\Controllers\AiAnalysisController::class, 'analyze'])->name('ai-analysis.analyze');
    Route::post('/ai-analysis/negate', [App\Http\Controllers\AiAnalysisController::class, 'batchNegate'])->name('ai-analysis.negate');
    
    // Rota para dados do gráfico do dashboard
    Route::get('/api/dashboard/new-terms-chart', [DashboardController::class, 'getNewTermsChartData'])->name('api.dashboard.new-terms-chart');

    // Rotas para fila e comandos
    Route::get('/queue-commands', [App\Http\Controllers\QueueCommandsController::class, 'index'])->name('queue-commands.index');
    Route::post('/queue-commands/execute', [App\Http\Controllers\QueueCommandsController::class, 'executeCommand'])->name('queue-commands.execute');
    
    // Incluir outras rotas de configurações
    require __DIR__.'/settings.php';

    // API Token Management UI
    Route::get('/api-tokens', [App\Http\Controllers\Api\ApiTokenUiController::class, 'index'])->name('api.tokens.ui');
    Route::post('/api-tokens', [App\Http\Controllers\Api\ApiTokenUiController::class, 'store'])->name('api.tokens.store');
    Route::patch('/api-tokens/{apiToken}/revoke', [App\Http\Controllers\Api\ApiTokenUiController::class, 'revoke'])->name('api.tokens.revoke');
    Route::put('/api-tokens/{apiToken}', [App\Http\Controllers\Api\ApiTokenUiController::class, 'update'])->name('api.tokens.update');
    Route::delete('/api-tokens/{apiToken}', [App\Http\Controllers\Api\ApiTokenUiController::class, 'destroy'])->name('api.tokens.destroy');
});
