<?php

namespace App\Jobs;

use App\Models\SearchTerm;
use App\Services\GoogleAdsQuotaService;
use Google\Ads\GoogleAds\Lib\V20\GoogleAdsClient;
use Google\Ads\GoogleAds\V20\Enums\SearchTermTargetingStatusEnum\SearchTermTargetingStatus;
use Google\Ads\GoogleAds\V20\Services\GoogleAdsRow;
use Google\Ads\GoogleAds\V20\Services\SearchGoogleAdsStreamRequest;
use Google\ApiCore\ApiException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class BatchSyncSearchTermStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = [60, 300, 600];

    protected string $startDate;

    protected string $endDate;

    public function __construct(?string $startDate = null, ?string $endDate = null)
    {
        $this->startDate = $startDate ?? config('googleads.absolute_start_date', '2000-01-01');
        $this->endDate = $endDate ?? Carbon::now()->format('Y-m-d');
    }

    public function handle(GoogleAdsClient $googleAdsClient, GoogleAdsQuotaService $quotaService): void
    {
        Log::info('BatchSyncSearchTermStatsJob: Iniciando sincronização em lote de estatísticas.', [
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ]);

        // Verificar cota antes da chamada
        if (!$quotaService->canMakeRequest()) {
            Log::warning('BatchSyncSearchTermStatsJob: Cota da API Google Ads excedida. Liberando job com delay de 60s.');
            $this->release(60);
            return;
        }

        // Obter o ID da conta do cliente
        $clientCustomerId = config('app.client_customer_id');
        if (empty($clientCustomerId)) {
            $iniPath = config('app.google_ads_php_path');
            if (file_exists($iniPath)) {
                $iniConfig = parse_ini_file($iniPath, true);
                $clientCustomerId = $iniConfig['GOOGLE_ADS']['clientCustomerId'] ?? null;
            }
        }

        if (empty($clientCustomerId)) {
            throw new \Exception('Client Customer ID is missing in configuration (config/app.php or google_ads_php.ini).');
        }

        try {
            $googleAdsServiceClient = $googleAdsClient->getGoogleAdsServiceClient();

            $query = "
                SELECT
                    search_term_view.search_term,
                    search_term_view.status,
                    metrics.impressions,
                    metrics.clicks,
                    metrics.cost_micros,
                    metrics.ctr,
                    campaign.id,
                    campaign.name,
                    ad_group.id,
                    ad_group.name
                FROM search_term_view
                WHERE segments.date BETWEEN '{$this->startDate}' AND '{$this->endDate}'
                  AND metrics.impressions > 0
                ORDER BY metrics.cost_micros DESC
            ";

            $request = new SearchGoogleAdsStreamRequest([
                'customer_id' => $clientCustomerId,
                'query' => $query,
            ]);

            $stream = $googleAdsServiceClient->searchStream($request);

            // Registrar uso da cota após chamada bem-sucedida
            $quotaService->recordRequest();

            $updatedCount = 0;
            $notFoundCount = 0;
            $errorCount = 0;

            foreach ($stream->iterateAllElements() as $googleAdsRow) {
                /** @var GoogleAdsRow $googleAdsRow */
                try {
                    $searchTermView = $googleAdsRow->getSearchTermView();
                    $metrics = $googleAdsRow->getMetrics();
                    $campaign = $googleAdsRow->getCampaign();
                    $adGroup = $googleAdsRow->getAdGroup();

                    $searchTermText = $searchTermView->getSearchTerm();
                    $campaignId = $campaign->getId();
                    $adGroupId = $adGroup->getId();

                    $statusEnum = $searchTermView->getStatus();
                    $status = is_int($statusEnum) ? SearchTermTargetingStatus::name($statusEnum) : 'UNKNOWN';

                    $impressions = $metrics->getImpressions();
                    $clicks = $metrics->getClicks();
                    $costMicros = $metrics->getCostMicros();
                    $ctr = $metrics->getCtr() * 100;

                    // Buscar o registro existente por chave única
                    $existingTerm = SearchTerm::where('campaign_id', $campaignId)
                        ->where('ad_group_id', $adGroupId)
                        ->where('search_term', $searchTermText)
                        ->first();

                    if ($existingTerm) {
                        $existingTerm->update([
                            'impressions' => $impressions,
                            'clicks' => $clicks,
                            'cost_micros' => $costMicros,
                            'ctr' => $ctr,
                            'status' => $status,
                            'campaign_name' => $campaign->getName(),
                            'ad_group_name' => $adGroup->getName(),
                            'statistics_synced_at' => now(),
                        ]);
                        $updatedCount++;
                    } else {
                        $notFoundCount++;
                    }

                    // Log de progresso a cada 500 termos
                    if (($updatedCount + $notFoundCount) % 500 === 0) {
                        Log::info('BatchSyncSearchTermStatsJob: Progresso...', [
                            'processed' => $updatedCount + $notFoundCount,
                            'updated' => $updatedCount,
                            'not_found' => $notFoundCount,
                        ]);
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::error('BatchSyncSearchTermStatsJob: Erro ao processar termo.', [
                        'message' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('BatchSyncSearchTermStatsJob: Sincronização em lote concluída.', [
                'updated' => $updatedCount,
                'not_found_in_db' => $notFoundCount,
                'errors' => $errorCount,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
            ]);
        } catch (ApiException $apiException) {
            Log::error('BatchSyncSearchTermStatsJob: Google Ads API Exception.', [
                'status' => $apiException->getStatus(),
                'message' => $apiException->getBasicMessage(),
                'details' => $apiException->getMetadata(),
            ]);
            $this->fail($apiException);
        } catch (\Exception $exception) {
            Log::error('BatchSyncSearchTermStatsJob: Exceção inesperada.', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
            $this->fail($exception);
        }
    }
}
