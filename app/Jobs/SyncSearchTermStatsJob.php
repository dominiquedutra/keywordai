<?php

namespace App\Jobs;

use App\Exceptions\GoogleAdsQuotaExceededException;
use App\Models\SearchTerm;
use App\Services\GoogleAdsQuotaService;
use Google\Ads\GoogleAds\Lib\V19\GoogleAdsClient;
use Google\Ads\GoogleAds\V19\Enums\SearchTermTargetingStatusEnum\SearchTermTargetingStatus;
use Google\Ads\GoogleAds\V19\Services\GoogleAdsRow;
use Google\Ads\GoogleAds\V19\Services\SearchGoogleAdsStreamRequest;
use Google\ApiCore\ApiException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SyncSearchTermStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * O modelo SearchTerm a ser atualizado.
     *
     * @var \App\Models\SearchTerm
     */
    protected $searchTerm;

    /**
     * O serviço de controle de cotas da API Google Ads.
     *
     * @var GoogleAdsQuotaService
     */
    protected $quotaService;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\SearchTerm $searchTerm
     */
    public function __construct(SearchTerm $searchTerm)
    {
        $this->searchTerm = $searchTerm;
    }

    /**
     * Execute the job.
     *
     * @param GoogleAdsClient $googleAdsClient
     * @param GoogleAdsQuotaService $quotaService
     * @return void
     */
    public function handle(GoogleAdsClient $googleAdsClient, GoogleAdsQuotaService $quotaService): void
    {
        try {
            // Chama o método síncrono que contém a lógica principal
            $this->handleSynchronous($googleAdsClient, $quotaService);
        } catch (GoogleAdsQuotaExceededException $quotaException) {
            // Log específico para cota excedida e release
            Log::info('SyncSearchTermStatsJob: Cota da API Google Ads excedida. Liberando job de volta para a fila com atraso de 60 segundos.', [
                'message' => $quotaException->getMessage(),
                'term' => $this->searchTerm->search_term ?? 'N/A',
                'term_id' => $this->searchTerm->id ?? null
            ]);
            $this->release(60); // Libera com delay de 60 segundos
        } catch (\Google\ApiCore\ApiException $apiException) {
            // Captura exceções específicas da API Google Ads
            Log::error('SyncSearchTermStatsJob: Google Ads API Exception ao buscar estatísticas do termo:', [
                'status' => $apiException->getStatus(),
                'message' => $apiException->getBasicMessage(),
                'details' => $apiException->getMetadata(),
                'term' => $this->searchTerm->search_term ?? 'N/A',
                'term_id' => $this->searchTerm->id ?? null
            ]);
            $this->fail($apiException); // Notifica a fila sobre a falha
        } catch (\Exception $exception) {
            // Captura qualquer outra exceção
            Log::error('SyncSearchTermStatsJob: Exceção inesperada ao buscar estatísticas do termo:', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'term' => $this->searchTerm->search_term ?? 'N/A',
                'term_id' => $this->searchTerm->id ?? null
            ]);
            $this->fail($exception); // Notifica a fila sobre a falha
        }
    }

    /**
     * Executa a lógica do job de forma síncrona e retorna o modelo SearchTerm atualizado.
     *
     * @param GoogleAdsClient $googleAdsClient
     * @param GoogleAdsQuotaService $quotaService
     * @return SearchTerm
     * @throws \Exception
     */
    public function handleSynchronous(GoogleAdsClient $googleAdsClient, GoogleAdsQuotaService $quotaService): SearchTerm
    {
        $this->quotaService = $quotaService;
        
        // Obter o termo de pesquisa do modelo
        $term = $this->searchTerm->search_term;
        Log::info("SyncSearchTermStatsJob: Buscando estatísticas para o termo: '{$term}'");

        // Definir datas: desde o início absoluto até hoje
        $startDate = Carbon::parse(config('googleads.absolute_start_date', '2000-01-01'));
        $endDate = Carbon::now();

        // Formatar as datas
        $startDateFormatted = $startDate->format('Y-m-d');
        $endDateFormatted = $endDate->format('Y-m-d');

        Log::info("SyncSearchTermStatsJob: Período de consulta: {$startDateFormatted} até {$endDateFormatted}");

        try {
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
                Log::error('SyncSearchTermStatsJob: Client Customer ID não encontrado na configuração (config/app.php ou google_ads_php.ini).');
                throw new \Exception('Client Customer ID não configurado.');
            }
            Log::info('SyncSearchTermStatsJob: Usando Client Customer ID: ' . $clientCustomerId);

            // Verificar se há cota disponível antes de fazer a chamada à API
            if (!$this->quotaService->canMakeRequest()) {
                Log::warning("SyncSearchTermStatsJob: Cota da API Google Ads excedida.");
                throw new GoogleAdsQuotaExceededException('Cota da API Google Ads excedida.');
            }
            
            // Criar o serviço GoogleAdsServiceClient
            $googleAdsServiceClient = $googleAdsClient->getGoogleAdsServiceClient();

            // Obter os IDs de campanha e grupo de anúncios do modelo SearchTerm
            $campaignId = $this->searchTerm->campaign_id;
            $adGroupId = $this->searchTerm->ad_group_id;

            // Construir os nomes de recurso completos
            $campaignResourceName = "customers/{$clientCustomerId}/campaigns/{$campaignId}";
            $adGroupResourceName = "customers/{$clientCustomerId}/adGroups/{$adGroupId}";
            
            Log::info("SyncSearchTermStatsJob: Buscando estatísticas para o termo '{$term}' no contexto: Campanha [{$campaignResourceName}], Grupo de Anúncios [{$adGroupResourceName}]");
            
            // Construir a consulta GAQL usando uma abordagem diferente
            // Vamos tentar usar keyword_view em vez de search_term_view para obter métricas específicas
            $query = "
                SELECT
                    keyword_view.resource_name,
                    ad_group_criterion.criterion_id,
                    ad_group_criterion.keyword.text,
                    ad_group_criterion.keyword.match_type,
                    ad_group.id,
                    ad_group.name,
                    campaign.id,
                    campaign.name,
                    metrics.impressions,
                    metrics.clicks,
                    metrics.cost_micros
                FROM keyword_view
                WHERE ad_group_criterion.keyword.text = '{$term}'
                  AND campaign.id = {$campaignId}
                  AND ad_group.id = {$adGroupId}
                  AND segments.date BETWEEN '{$startDateFormatted}' AND '{$endDateFormatted}'
            ";

            // Se a consulta acima falhar, vamos tentar uma abordagem alternativa
            // Vamos fazer uma consulta mais simples e filtrar os resultados no código
            if (true) { // Sempre executar esta consulta alternativa por enquanto
                $query = "
                    SELECT
                        search_term_view.search_term,
                        search_term_view.status,
                        metrics.cost_micros,
                        metrics.impressions,
                        metrics.clicks,
                        campaign.id,
                        campaign.name,
                        ad_group.id,
                        ad_group.name
                    FROM search_term_view
                    WHERE search_term_view.search_term = '{$term}'
                      AND segments.date BETWEEN '{$startDateFormatted}' AND '{$endDateFormatted}'
                ";
            }

            Log::debug("SyncSearchTermStatsJob: Consulta GAQL: {$query}");

            // Criar a requisição de stream
            $request = new SearchGoogleAdsStreamRequest([
                'customer_id' => $clientCustomerId,
                'query' => $query
            ]);

            // Executar a consulta usando searchStream
            $stream = $googleAdsServiceClient->searchStream($request);
            
            // Registra o uso da cota após uma chamada bem-sucedida
            $this->quotaService->recordRequest();

            // Inicializar variáveis para agregação
            $totalCostMicros = 0;
            $totalImpressions = 0;
            $totalClicks = 0;
            $latestStatus = null;
            $rowCount = 0;

            // Armazenar métricas por campanha e grupo de anúncios
            $metricsByCampaignAndAdGroup = [];
            
            // Iterar sobre os resultados do stream
            foreach ($stream->iterateAllElements() as $googleAdsRow) {
                /** @var GoogleAdsRow $googleAdsRow */
                try {
                    $searchTermView = $googleAdsRow->getSearchTermView();
                    $metrics = $googleAdsRow->getMetrics();
                    $campaign = $googleAdsRow->getCampaign();
                    $adGroup = $googleAdsRow->getAdGroup();
                    
                    // Obter os IDs de campanha e grupo de anúncios
                    $rowCampaignId = $campaign->getId();
                    $rowAdGroupId = $adGroup->getId();
                    $rowCampaignName = $campaign->getName();
                    $rowAdGroupName = $adGroup->getName();
                    
                    // Criar uma chave única para esta combinação de campanha e grupo de anúncios
                    $key = "{$rowCampaignId}_{$rowAdGroupId}";
                    
                    Log::debug("SyncSearchTermStatsJob: Linha recebida - Termo: '{$searchTermView->getSearchTerm()}', Campanha: {$rowCampaignId} ({$rowCampaignName}), Grupo: {$rowAdGroupId} ({$rowAdGroupName}), Impressões: {$metrics->getImpressions()}, Cliques: {$metrics->getClicks()}, Custo: {$metrics->getCostMicros()}");
                    
                    // Inicializar a entrada para esta combinação se ainda não existir
                    if (!isset($metricsByCampaignAndAdGroup[$key])) {
                        $metricsByCampaignAndAdGroup[$key] = [
                            'campaign_id' => $rowCampaignId,
                            'campaign_name' => $rowCampaignName,
                            'ad_group_id' => $rowAdGroupId,
                            'ad_group_name' => $rowAdGroupName,
                            'impressions' => 0,
                            'clicks' => 0,
                            'cost_micros' => 0,
                            'status' => null,
                            'row_count' => 0
                        ];
                    }
                    
                    // Atribuir métricas diretamente (não precisamos mais somar, pois a API já retorna dados agregados)
                    $metricsByCampaignAndAdGroup[$key]['impressions'] = $metrics->getImpressions();
                    $metricsByCampaignAndAdGroup[$key]['clicks'] = $metrics->getClicks();
                    $metricsByCampaignAndAdGroup[$key]['cost_micros'] = $metrics->getCostMicros();
                    $metricsByCampaignAndAdGroup[$key]['row_count'] = 1; // Sempre 1 agora, pois temos apenas uma linha por combinação
                    
                    // Capturar o status (não há mais ordenação por data, então pegamos diretamente)
                    $statusEnum = $searchTermView->getStatus();
                    $metricsByCampaignAndAdGroup[$key]['status'] = is_int($statusEnum) ? SearchTermTargetingStatus::name($statusEnum) : 'UNKNOWN';
                    
                } catch (\Exception $e) {
                    Log::error('SyncSearchTermStatsJob: Erro ao processar linha: ' . $e->getMessage());
                    // Continuar com a próxima linha
                }
            }
            
            // Encontrar a entrada para a combinação específica que estamos buscando
            $key = "{$campaignId}_{$adGroupId}";
            if (isset($metricsByCampaignAndAdGroup[$key])) {
                $metrics = $metricsByCampaignAndAdGroup[$key];
                $totalImpressions = $metrics['impressions'];
                $totalClicks = $metrics['clicks'];
                $totalCostMicros = $metrics['cost_micros'];
                $latestStatus = $metrics['status'];
                $rowCount = $metrics['row_count'];
                
                Log::info("SyncSearchTermStatsJob: Encontradas estatísticas específicas para o termo '{$term}' na campanha {$campaignId} e grupo de anúncios {$adGroupId}.");
            } else {
                Log::warning("SyncSearchTermStatsJob: Não foram encontradas estatísticas específicas para o termo '{$term}' na campanha {$campaignId} e grupo de anúncios {$adGroupId}.");
                
                // Exibir todas as combinações encontradas para diagnóstico
                foreach ($metricsByCampaignAndAdGroup as $key => $metrics) {
                    Log::debug("SyncSearchTermStatsJob: Combinação encontrada - Campanha: {$metrics['campaign_id']} ({$metrics['campaign_name']}), Grupo: {$metrics['ad_group_id']} ({$metrics['ad_group_name']}), Impressões: {$metrics['impressions']}, Cliques: {$metrics['clicks']}, Custo: {$metrics['cost_micros']}");
                }
                
                // Não encontramos dados para esta combinação específica
                $totalImpressions = 0;
                $totalClicks = 0;
                $totalCostMicros = 0;
                $latestStatus = 'UNKNOWN';
                $rowCount = 0;
            }

            // Verificar se encontramos algum dado
            if ($rowCount === 0) {
                Log::info("SyncSearchTermStatsJob: Nenhum dado encontrado para o termo '{$term}' no período especificado.");
                return $this->searchTerm; // Retorna o modelo sem alterações
            }

            // Calcular CTR
            $ctr = $totalImpressions > 0 ? ($totalClicks / $totalImpressions) * 100 : 0;

            // Atualizar o modelo SearchTerm com os dados obtidos
            $this->searchTerm->impressions = $totalImpressions;
            $this->searchTerm->clicks = $totalClicks;
            $this->searchTerm->cost_micros = $totalCostMicros;
            $this->searchTerm->ctr = $ctr;
            $this->searchTerm->status = $latestStatus;
            $this->searchTerm->statistics_synced_at = now(); // Registra o momento da sincronização
            $this->searchTerm->save();

            Log::info("SyncSearchTermStatsJob: Estatísticas atualizadas com sucesso para o termo '{$term}'.", [
                'impressions' => $totalImpressions,
                'clicks' => $totalClicks,
                'cost_micros' => $totalCostMicros,
                'ctr' => $ctr,
                'status' => $latestStatus,
                'statistics_synced_at' => now()->toDateTimeString()
            ]);
            
            // Retorna o modelo atualizado
            return $this->searchTerm;

        } catch (ApiException $apiException) {
            Log::error('SyncSearchTermStatsJob: Google Ads API Exception ao buscar estatísticas do termo:', [
                'status' => $apiException->getStatus(),
                'message' => $apiException->getBasicMessage(),
                'details' => $apiException->getMetadata(),
                'term' => $term,
                'query' => $query ?? 'N/A'
            ]);
            throw $apiException;
        } catch (\Exception $exception) {
            Log::error('SyncSearchTermStatsJob: Exceção inesperada ao buscar estatísticas do termo:', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'term' => $term
            ]);
            throw $exception;
        }
    }
}
