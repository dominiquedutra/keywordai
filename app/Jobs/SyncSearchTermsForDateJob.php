<?php

namespace App\Jobs;

use App\Jobs\SendNewSearchTermNotificationJob;
use App\Models\SearchTerm;
use App\Models\SearchTermSyncDate;
use App\Services\GoogleAdsQuotaService;
use Carbon\Carbon;
use DateTimeInterface;
use Google\Ads\GoogleAds\Lib\V20\GoogleAdsClient;
use Google\Ads\GoogleAds\V20\Enums\SearchTermTargetingStatusEnum\SearchTermTargetingStatus;
use Google\Ads\GoogleAds\V20\Enums\KeywordMatchTypeEnum\KeywordMatchType;
use Google\Ads\GoogleAds\V20\Services\GoogleAdsRow;
use Google\Ads\GoogleAds\V20\Services\SearchGoogleAdsStreamRequest;
use Google\ApiCore\ApiException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SyncSearchTermsForDateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * A data para sincronização.
     *
     * @var \DateTimeInterface
     */
    protected $syncDate;

    /**
     * O número de tentativas para este job.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * O número de segundos para esperar antes de tentar novamente.
     *
     * @var array<int, int>
     */
    public $backoff = [60, 300, 600]; // 1 min, 5 min, 10 min

    /**
     * Create a new job instance.
     *
     * @param \DateTimeInterface $syncDate
     * @return void
     */
    public function __construct(DateTimeInterface $syncDate)
    {
        $this->syncDate = $syncDate;
    }

    /**
     * Execute the job.
     *
     * @param \Google\Ads\GoogleAds\Lib\V20\GoogleAdsClient $googleAdsClient
     * @param \App\Services\GoogleAdsQuotaService $quotaService
     * @return void
     */
    public function handle(GoogleAdsClient $googleAdsClient, GoogleAdsQuotaService $quotaService): void
    {
        $syncDateFormatted = $this->syncDate->format('Y-m-d');
        
        Log::info("Iniciando sincronização de termos de pesquisa para a data: {$syncDateFormatted}");
        
        // Obter ou criar o registro de controle da data
        $syncDateRecord = null;
        
        try {
            // Buscar o registro existente usando whereRaw para comparar apenas a parte da data
            Log::info("Buscando registro para a data {$syncDateFormatted} usando whereRaw com DATE()");
            
            $syncDateRecord = SearchTermSyncDate::whereRaw("DATE(sync_date) = ?", [$syncDateFormatted])->first();
            
            if ($syncDateRecord) {
                // Registro encontrado
                Log::info("Registro existente encontrado. ID: {$syncDateRecord->id}, Status: {$syncDateRecord->status}");
            } else {
                // Registro não encontrado, criar um novo
                Log::info("Nenhum registro encontrado para a data {$syncDateFormatted}. Criando novo registro.");
                $syncDateRecord = new SearchTermSyncDate();
                $syncDateRecord->sync_date = $this->syncDate;
                $syncDateRecord->status = 'pending';
                $syncDateRecord->attempts = 0;
                $syncDateRecord->save();
                Log::info("Novo registro criado com sucesso. ID: {$syncDateRecord->id}, Status: {$syncDateRecord->status}");
            }
            
            // Marcar como processando para esta execução
            $syncDateRecord->markAsProcessing($this->job ? $this->job->getJobId() : null);
            Log::info("Status atualizado para 'processing' para a data {$syncDateFormatted}. Tentativas: {$syncDateRecord->attempts}");
            
        } catch (\Exception $e) {
            Log::error("Erro crítico ao obter/criar registro para a data {$syncDateFormatted}: {$e->getMessage()}");
            // Falhar o job imediatamente se não conseguirmos nem obter/criar o registro de controle
            $this->fail($e);
            return; // Importante sair após falhar
        }
        
        try {
            // Verificar se há cota disponível antes de fazer a chamada à API
            if (!$quotaService->canMakeRequest()) {
                Log::warning("Cota da API Google Ads excedida. Liberando o job de volta para a fila com delay.");
                $this->release(60); // Libera o job de volta para a fila com 60 segundos de delay
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
            
            // Criar o serviço GoogleAdsServiceClient
            $googleAdsServiceClient = $googleAdsClient->getGoogleAdsServiceClient();
            
            // Construir a consulta GAQL com os campos válidos
            $query = "
                SELECT
                    search_term_view.search_term,
                    search_term_view.status,
                    segments.keyword.info.text,
                    segments.keyword.info.match_type,
                    metrics.impressions,
                    metrics.clicks,
                    metrics.cost_micros,
                    metrics.ctr,
                    segments.date,
                    campaign.id,
                    campaign.name,
                    ad_group.id,
                    ad_group.name
                FROM search_term_view
                WHERE segments.date = '{$syncDateFormatted}'
                  AND metrics.impressions > 0
                ORDER BY metrics.impressions DESC
            ";
            
            // Criar a requisição de stream
            $request = new SearchGoogleAdsStreamRequest([
                'customer_id' => $clientCustomerId,
                'query' => $query
            ]);
            
            // Executar a consulta usando searchStream
            $stream = $googleAdsServiceClient->searchStream($request);
            
            // Registra o uso da cota após uma chamada bem-sucedida
            $quotaService->recordRequest();
            
            $termCount = 0;
            $savedCount = 0;
            $updatedCount = 0;
            
            // Iterar sobre os resultados do stream
            foreach ($stream->iterateAllElements() as $googleAdsRow) {
                /** @var GoogleAdsRow $googleAdsRow */
                try {
                    $searchTermView = $googleAdsRow->getSearchTermView();
                    $metrics = $googleAdsRow->getMetrics();
                    $campaign = $googleAdsRow->getCampaign();
                    $adGroup = $googleAdsRow->getAdGroup();
                    $segments = $googleAdsRow->getSegments();
                    
                    $searchTerm = $searchTermView->getSearchTerm();
                    
                    // Obter e converter Status Enum
                    $statusEnum = $searchTermView->getStatus();
                    $status = is_int($statusEnum) ? SearchTermTargetingStatus::name($statusEnum) : 'UNKNOWN';
                    
                    // Obter o texto da keyword e o tipo de correspondência dos segmentos
                    $keywordText = '';
                    $matchType = '';
                    
                    if ($segments->hasKeyword() && $segments->getKeyword()->hasInfo()) {
                        $keywordInfo = $segments->getKeyword()->getInfo();
                        $keywordText = $keywordInfo->getText();
                        $matchTypeEnum = $keywordInfo->getMatchType();
                        $matchType = is_int($matchTypeEnum) ? KeywordMatchType::name($matchTypeEnum) : 'UNKNOWN';
                    } else {
                        $keywordText = 'N/A';
                        $matchType = 'N/A';
                    }
                    
                    $impressions = $metrics->getImpressions();
                    $clicks = $metrics->getClicks();
                    $costMicros = $metrics->getCostMicros();
                    $ctr = $metrics->getCtr() * 100;
                    $campaignId = $campaign->getId();
                    $campaignName = $campaign->getName();
                    $adGroupId = $adGroup->getId();
                    $adGroupName = $adGroup->getName();
                    $segmentDate = $segments->getDate();
                    
                    $termCount++;
                    
                    // Verificar se o termo já existe para esta combinação de campanha e grupo de anúncios
                    $existingTerm = SearchTerm::where('campaign_id', $campaignId)
                        ->where('ad_group_id', $adGroupId)
                        ->where('search_term', $searchTerm)
                        ->first();
                    
                    if ($existingTerm) {
                        // Atualizar o termo existente com os novos dados
                        $existingTerm->update([
                            'impressions' => $impressions,
                            'clicks' => $clicks,
                            'cost_micros' => $costMicros,
                            'ctr' => $ctr,
                            'status' => $status,
                            'keyword_text' => $keywordText,
                            'match_type' => $matchType,
                            'statistics_synced_at' => now(),
                        ]);
                        $updatedCount++;
                    } else {
                        // Criar um novo registro e capturar a instância
                        $newSearchTerm = SearchTerm::create([
                            'search_term' => $searchTerm,
                            'keyword_text' => $keywordText,
                            'match_type' => $matchType,
                            'impressions' => $impressions,
                            'clicks' => $clicks,
                            'cost_micros' => $costMicros,
                            'ctr' => $ctr,
                            'status' => $status,
                            'campaign_id' => $campaignId,
                            'campaign_name' => $campaignName,
                            'ad_group_id' => $adGroupId,
                            'ad_group_name' => $adGroupName,
                            'first_seen_at' => $segmentDate,
                            'notified_at' => null, // Garantir que notified_at seja null para novos termos
                            'statistics_synced_at' => now(),
                        ]);
                        $savedCount++;
                        
                        // Verificar se as notificações do Google Chat estão habilitadas
                        if (config('app.send_google_chat_notifications')) {
                            try {
                                // Despachar o job para enviar a notificação
                                SendNewSearchTermNotificationJob::dispatch(
                                    $searchTerm,
                                    $campaignName,
                                    $adGroupName,
                                    $keywordText,
                                    $newSearchTerm->id,
                                    $campaignId,
                                    $adGroupId
                                )->onQueue('notifications');
                                
                                // Marcar o termo como notificado
                                $newSearchTerm->notified_at = now();
                                $newSearchTerm->save();
                                
                                Log::info("Notificação despachada para o novo termo: '{$searchTerm}'", [
                                    'search_term_id' => $newSearchTerm->id,
                                    'campaign' => $campaignName,
                                    'ad_group' => $adGroupName
                                ]);
                            } catch (\Exception $notificationException) {
                                Log::error("Erro ao despachar notificação para o termo: '{$searchTerm}'", [
                                    'message' => $notificationException->getMessage(),
                                    'trace' => $notificationException->getTraceAsString(),
                                    'search_term_id' => $newSearchTerm->id
                                ]);
                                // Não falhar o job principal por causa de um erro na notificação
                            }
                        } else {
                            Log::info("Notificação suprimida para o termo '{$searchTerm}' devido à configuração", [
                                'config' => 'app.send_google_chat_notifications=false'
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Erro ao processar termo de pesquisa: {$e->getMessage()}", [
                        'sync_date' => $syncDateFormatted,
                        'exception' => $e,
                    ]);
                    // Continuar com o próximo item
                }
            }
            
            // Registrar os resultados
            Log::info("Sincronização concluída para a data {$syncDateFormatted}", [
                'total_terms' => $termCount,
                'new_terms' => $savedCount,
                'updated_terms' => $updatedCount,
            ]);
            
            // Atualizar o status para 'completed'
            $syncDateRecord->markAsCompleted();
            
        } catch (ApiException $apiException) {
            Log::error('Google Ads API Exception ao sincronizar termos de pesquisa:', [
                'sync_date' => $syncDateFormatted,
                'status' => $apiException->getStatus(),
                'message' => $apiException->getBasicMessage(),
                'details' => $apiException->getMetadata(),
            ]);
            
            // Atualizar o status para 'failed'
            $syncDateRecord->markAsFailed("API Error: {$apiException->getBasicMessage()}");
            
            // Falhar o job para que a fila possa tentar novamente
            $this->fail($apiException);
        } catch (\Exception $exception) {
            Log::error('Erro inesperado ao sincronizar termos de pesquisa:', [
                'sync_date' => $syncDateFormatted,
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
            
            // Atualizar o status para 'failed'
            $syncDateRecord->markAsFailed("Error: {$exception->getMessage()}");
            
            // Falhar o job para que a fila possa tentar novamente
            $this->fail($exception);
        }
    }
}
