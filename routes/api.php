<?php

use App\Http\Controllers\Api\AdGroupApiController;
use App\Http\Controllers\Api\AiAnalysisApiController;
use App\Http\Controllers\Api\CampaignApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\HealthApiController;
use App\Http\Controllers\Api\NegativeKeywordApiController;
use App\Http\Controllers\Api\SearchTermApiController;
use App\Http\Controllers\Api\SyncApiController;
use App\Http\Controllers\Api\TokenManagementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rotas da API protegidas por token de autenticação.
| Use o header X-API-Token ou Authorization: Bearer {token}
|
*/

// Rotas públicas (sem autenticação)
Route::get('/health', [HealthApiController::class, 'check']);
Route::get('/info', [HealthApiController::class, 'info']);

// Grupo de rotas protegidas
Route::middleware(['api_token'])->group(function () {
    
    // Health & Info do token atual
    Route::get('/token/me', [TokenManagementController::class, 'me']);

    // Dashboard
    Route::get('/dashboard/metrics', [DashboardApiController::class, 'metrics']);
    Route::get('/dashboard/chart/new-terms', [DashboardApiController::class, 'newTermsChart']);
    Route::get('/dashboard/top-terms', [DashboardApiController::class, 'topSearchTerms']);
    Route::get('/dashboard/activity', [DashboardApiController::class, 'recentActivity']);

    // Search Terms
    Route::get('/search-terms', [SearchTermApiController::class, 'index']);
    Route::get('/search-terms/stats', [SearchTermApiController::class, 'stats']);
    Route::post('/search-terms/batch-negate', [SearchTermApiController::class, 'batchNegate']);
    Route::get('/search-terms/{searchTerm}', [SearchTermApiController::class, 'show']);
    Route::post('/search-terms/{searchTerm}/refresh', [SearchTermApiController::class, 'refreshStats']);
    Route::post('/search-terms/{searchTerm}/negate', [SearchTermApiController::class, 'addAsNegative']);
    Route::post('/search-terms/{searchTerm}/add-positive', [SearchTermApiController::class, 'addAsPositive']);

    // Campaigns
    Route::get('/campaigns', [CampaignApiController::class, 'index']);
    Route::get('/campaigns/{campaign}', [CampaignApiController::class, 'show']);
    Route::get('/campaigns/{campaign}/ad-groups', [CampaignApiController::class, 'adGroups']);
    Route::get('/campaigns/{campaign}/search-terms', [CampaignApiController::class, 'searchTerms']);
    Route::get('/campaigns/{campaign}/stats', [CampaignApiController::class, 'stats']);

    // Ad Groups
    Route::get('/ad-groups', [AdGroupApiController::class, 'index']);
    Route::get('/ad-groups/{adGroup}', [AdGroupApiController::class, 'show']);
    Route::get('/ad-groups/{adGroup}/search-terms', [AdGroupApiController::class, 'searchTerms']);

    // Negative Keywords
    Route::get('/negative-keywords', [NegativeKeywordApiController::class, 'index']);
    Route::get('/negative-keywords/stats', [NegativeKeywordApiController::class, 'stats']);
    Route::post('/negative-keywords', [NegativeKeywordApiController::class, 'store']);
    Route::post('/negative-keywords/batch', [NegativeKeywordApiController::class, 'batchStore']);
    Route::get('/negative-keywords/{negativeKeyword}', [NegativeKeywordApiController::class, 'show']);

    // Sync Operations
    Route::post('/sync/search-terms', [SyncApiController::class, 'syncSearchTerms']);
    Route::post('/sync/search-terms-range', [SyncApiController::class, 'syncSearchTermsRange']);
    Route::post('/sync/entities', [SyncApiController::class, 'syncEntities']);
    Route::get('/sync/status', [SyncApiController::class, 'syncStatus']);
    Route::get('/sync/queue-status', [SyncApiController::class, 'queueStatus']);

    // AI Analysis
    Route::get('/ai/models', [AiAnalysisApiController::class, 'availableModels']);
    Route::post('/ai/analyze', [AiAnalysisApiController::class, 'analyze']);
    Route::post('/ai/suggest-negatives', [AiAnalysisApiController::class, 'suggestNegatives']);
});

// Rotas de administração de tokens (requer permissão 'admin')
Route::middleware(['api_token:admin'])->prefix('admin')->group(function () {
    Route::get('/tokens', [TokenManagementController::class, 'index']);
    Route::post('/tokens', [TokenManagementController::class, 'store'])->name('api.tokens.create');
    Route::get('/tokens/{apiToken}', [TokenManagementController::class, 'show']);
    Route::put('/tokens/{apiToken}', [TokenManagementController::class, 'update']);
    Route::delete('/tokens/{apiToken}', [TokenManagementController::class, 'destroy']);
});
