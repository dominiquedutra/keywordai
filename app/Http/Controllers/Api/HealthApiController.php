<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class HealthApiController extends BaseApiController
{
    /**
     * Verificar saúde da API.
     */
    public function check(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'queue' => $this->checkQueue(),
            'google_ads_config' => $this->checkGoogleAdsConfig(),
        ];

        $allHealthy = collect($checks)->every(function ($check) {
            return $check['status'] === 'ok';
        });

        return response()->json([
            'success' => $allHealthy,
            'status' => $allHealthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'checks' => $checks,
        ], $allHealthy ? 200 : 503);
    }

    /**
     * Informações da API.
     */
    public function info(): JsonResponse
    {
        return $this->successResponse([
            'name' => 'KeywordAI API',
            'version' => '1.0.0',
            'environment' => config('app.env'),
            'timezone' => config('app.timezone'),
            'features' => [
                'search_terms' => true,
                'campaigns' => true,
                'ad_groups' => true,
                'negative_keywords' => true,
                'sync' => true,
                'ai_analysis' => true,
            ],
            'google_ads' => [
                'client_customer_id' => config('app.client_customer_id'),
                'configured' => !empty(config('app.google_ads_php_path')) && file_exists(config('app.google_ads_php_path')),
            ],
            'ai_providers' => [
                'gemini' => !empty(setting('ai_gemini_api_key')) || !empty(config('ai.models.gemini.api_key')),
                'openai' => !empty(setting('ai_openai_api_key')) || !empty(config('ai.models.openai.api_key')),
                'openrouter' => !empty(setting('ai_openrouter_api_key')) || !empty(config('ai.models.openrouter.api_key')),
            ],
            'rate_limits' => [
                'google_ads_daily' => 14000,
                'google_ads_per_minute' => 60,
            ],
        ]);
    }

    /**
     * Verificar banco de dados.
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            return [
                'status' => 'ok',
                'message' => 'Database connection successful',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verificar fila.
     */
    private function checkQueue(): array
    {
        try {
            $pendingJobs = DB::table('jobs')->count();
            $failedJobs = DB::table('failed_jobs')->count();

            return [
                'status' => 'ok',
                'message' => 'Queue operational',
                'pending_jobs' => $pendingJobs,
                'failed_jobs' => $failedJobs,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Queue check failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verificar configuração do Google Ads.
     */
    private function checkGoogleAdsConfig(): array
    {
        $iniPath = config('app.google_ads_php_path');

        if (empty($iniPath)) {
            return [
                'status' => 'warning',
                'message' => 'Google Ads config path not set',
            ];
        }

        if (!file_exists($iniPath)) {
            return [
                'status' => 'error',
                'message' => 'Google Ads config file not found',
            ];
        }

        return [
            'status' => 'ok',
            'message' => 'Google Ads configuration found',
            'client_customer_id' => config('app.client_customer_id'),
        ];
    }
}
