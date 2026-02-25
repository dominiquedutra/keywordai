<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Models\AdGroup;
use App\Services\GoogleAdsQuotaService;
use Google\Ads\GoogleAds\Lib\V19\GoogleAdsClient;
use Google\Ads\GoogleAds\V19\Enums\AdGroupStatusEnum\AdGroupStatus;
use Google\Ads\GoogleAds\V19\Enums\CampaignStatusEnum\CampaignStatus;
use Google\ApiCore\ApiException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncAdsEntitiesCommand extends Command
{
    /**
     * O nome e a assinatura do comando do console.
     *
     * @var string
     */
    protected $signature = 'googleads:sync-entities {--force : Forçar sincronização mesmo para entidades já existentes}';

    /**
     * A descrição do comando do console.
     *
     * @var string
     */
    protected $description = 'Sincroniza campanhas (ativas e pausadas) e grupos de anúncios ativos do Google Ads para o banco de dados local';

    /**
     * O cliente do Google Ads.
     *
     * @var GoogleAdsClient
     */
    protected $googleAdsClient;

    /**
     * O serviço de controle de cota do Google Ads.
     *
     * @var GoogleAdsQuotaService
     */
    protected $quotaService;

    /**
     * O ID do cliente do Google Ads.
     *
     * @var string
     */
    protected $clientCustomerId;

    /**
     * Contador de campanhas sincronizadas.
     *
     * @var int
     */
    protected $campaignsCount = 0;

    /**
     * Contador de grupos de anúncios sincronizados.
     *
     * @var int
     */
    protected $adGroupsCount = 0;

    /**
     * Criar uma nova instância do comando.
     *
     * @param GoogleAdsClient $googleAdsClient
     * @param GoogleAdsQuotaService $quotaService
     * @return void
     */
    public function __construct(GoogleAdsClient $googleAdsClient, GoogleAdsQuotaService $quotaService)
    {
        parent::__construct();
        $this->googleAdsClient = $googleAdsClient;
        $this->quotaService = $quotaService;
    }

    /**
     * Executar o comando do console.
     *
     * @return int
     */
    public function handle()
    {
        // Obter o ID do cliente do Google Ads
        $this->clientCustomerId = config('app.client_customer_id');
        if (empty($this->clientCustomerId)) {
            $iniPath = config('app.google_ads_php_path');
            if (file_exists($iniPath)) {
                $iniConfig = parse_ini_file($iniPath, true);
                $this->clientCustomerId = $iniConfig['GOOGLE_ADS']['clientCustomerId'] ?? null;
            }
        }

        if (empty($this->clientCustomerId)) {
            $this->error('Client Customer ID não encontrado na configuração.');
            return 1;
        }

        $this->info("Iniciando sincronização de entidades do Google Ads para o cliente {$this->clientCustomerId}...");

        try {
            // Sincronizar campanhas ativas e pausadas
            $this->syncCampaigns();
            
            // Sincronizar grupos de anúncios ativos
            $this->syncAdGroups();

            $this->info("Sincronização concluída com sucesso!");
            $this->info("Campanhas sincronizadas: {$this->campaignsCount}");
            $this->info("Grupos de anúncios sincronizados: {$this->adGroupsCount}");

            return 0;
        } catch (ApiException $e) {
            $this->error("Erro na API do Google Ads: {$e->getMessage()}");
            Log::error("GoogleAdsApiException em SyncAdsEntitiesCommand: {$e->getMessage()}", [
                'details' => $e->getMetadata(),
                'client_customer_id' => $this->clientCustomerId
            ]);
            return 1;
        } catch (\Exception $e) {
            $this->error("Erro inesperado: {$e->getMessage()}");
            Log::error("Exception em SyncAdsEntitiesCommand: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
                'client_customer_id' => $this->clientCustomerId
            ]);
            return 1;
        }
    }

    /**
     * Sincronizar campanhas ativas e pausadas.
     *
     * @return void
     * @throws ApiException
     */
    protected function syncCampaigns()
    {
        $this->info("Sincronizando campanhas ativas e pausadas...");

        // Verificar se há cota disponível
        if (!$this->quotaService->canMakeRequest()) {
            $this->error("Cota da API do Google Ads excedida. Tente novamente mais tarde.");
            return;
        }

        // Construir a consulta GAQL para buscar campanhas ativas e pausadas
        // Nota: Anteriormente buscávamos apenas campanhas 'ENABLED', mas isso causava problemas
        // quando grupos de anúncios ativos pertenciam a campanhas pausadas. Agora buscamos
        // campanhas 'ENABLED' e 'PAUSED' para garantir que tenhamos todas as campanhas relevantes
        // com seus status corretos.
        $query = "
            SELECT 
                campaign.id, 
                campaign.resource_name, 
                campaign.name, 
                campaign.status, 
                campaign.start_date, 
                campaign.end_date, 
                campaign.advertising_channel_type
            FROM campaign
            WHERE campaign.status IN ('ENABLED', 'PAUSED')
            ORDER BY campaign.name
        ";

        // Executar a consulta
        $googleAdsServiceClient = $this->googleAdsClient->getGoogleAdsServiceClient();
        $request = new \Google\Ads\GoogleAds\V19\Services\SearchGoogleAdsStreamRequest([
            'customer_id' => $this->clientCustomerId,
            'query' => $query
        ]);
        $stream = $googleAdsServiceClient->searchStream($request);

        // Registrar o uso da cota
        $this->quotaService->recordRequest();

        // Processar os resultados
        $force = $this->option('force');
        $campaignsProcessed = 0;

        foreach ($stream->iterateAllElements() as $googleAdsRow) {
            $campaign = $googleAdsRow->getCampaign();
            $campaignId = $campaign->getId();
            
            // Verificar se a campanha já existe no banco de dados
            $existingCampaign = Campaign::where('google_campaign_id', $campaignId)->first();
            
            // Obter o status bruto e mapeado para logs
            $rawStatus = $campaign->getStatus();
            $mappedStatus = CampaignStatus::name($rawStatus);
            
            if ($existingCampaign && !$force) {
                $this->line("Campanha já existe: {$campaign->getName()} (ID: {$campaignId})");
                $this->info("Status da campanha na API: Raw={$rawStatus}, Mapped={$mappedStatus}");
                $this->info("Status da campanha no banco de dados: {$existingCampaign->status}");
            } else {
                $this->info("Status da campanha '{$campaign->getName()}': Raw={$rawStatus}, Mapped={$mappedStatus}");
                
                // Obter o tipo de canal de publicidade
                $advertisingChannelType = $campaign->getAdvertisingChannelType();
                $advertisingChannelTypeName = $advertisingChannelType ? \Google\Ads\GoogleAds\V19\Enums\AdvertisingChannelTypeEnum\AdvertisingChannelType::name($advertisingChannelType) : null;
                
                $this->info("Tipo de canal de publicidade da campanha '{$campaign->getName()}': {$advertisingChannelTypeName}");
                
                // Criar ou atualizar a campanha no banco de dados
                $campaignData = [
                    'google_campaign_id' => $campaignId,
                    'resource_name' => $campaign->getResourceName(),
                    'name' => $campaign->getName(),
                    'status' => $mappedStatus,
                    'start_date' => $campaign->getStartDate() ? $campaign->getStartDate() : null,
                    'end_date' => $campaign->getEndDate() ? $campaign->getEndDate() : null,
                    'advertising_channel_type' => $advertisingChannelTypeName,
                ];

                Campaign::updateOrCreate(
                    ['google_campaign_id' => $campaignId],
                    $campaignData
                );

                $this->line("Campanha " . ($existingCampaign ? "atualizada" : "criada") . ": {$campaign->getName()} (ID: {$campaignId})");
                $this->campaignsCount++;
            }

            $campaignsProcessed++;
        }

        $this->info("Processadas {$campaignsProcessed} campanhas.");
    }

    /**
     * Sincronizar grupos de anúncios ativos.
     *
     * @return void
     * @throws ApiException
     */
    protected function syncAdGroups()
    {
        $this->info("Sincronizando grupos de anúncios ativos...");

        // Verificar se há cota disponível
        if (!$this->quotaService->canMakeRequest()) {
            $this->error("Cota da API do Google Ads excedida. Tente novamente mais tarde.");
            return;
        }

        // Construir a consulta GAQL para buscar grupos de anúncios ativos
        $query = "
            SELECT 
                ad_group.id, 
                ad_group.resource_name, 
                ad_group.name, 
                ad_group.status,
                campaign.id,
                campaign.name,
                campaign.resource_name
            FROM ad_group
            WHERE ad_group.status = 'ENABLED'
            ORDER BY campaign.name, ad_group.name
        ";

        // Executar a consulta
        $googleAdsServiceClient = $this->googleAdsClient->getGoogleAdsServiceClient();
        $request = new \Google\Ads\GoogleAds\V19\Services\SearchGoogleAdsStreamRequest([
            'customer_id' => $this->clientCustomerId,
            'query' => $query
        ]);
        $stream = $googleAdsServiceClient->searchStream($request);

        // Registrar o uso da cota
        $this->quotaService->recordRequest();

        // Processar os resultados
        $force = $this->option('force');
        $adGroupsProcessed = 0;

        foreach ($stream->iterateAllElements() as $googleAdsRow) {
            $adGroup = $googleAdsRow->getAdGroup();
            $campaign = $googleAdsRow->getCampaign();
            $adGroupId = $adGroup->getId();
            $campaignId = $campaign->getId();
            
            // Buscar a campanha no banco de dados
            $dbCampaign = Campaign::where('google_campaign_id', $campaignId)->first();
            
            // Obter o status da campanha na API para logs
            $campaignRawStatus = $campaign->getStatus();
            $campaignMappedStatus = CampaignStatus::name($campaignRawStatus);
            $this->info("Status da campanha '{$campaign->getName()}' na API: Raw={$campaignRawStatus}, Mapped={$campaignMappedStatus}");
            
            // Nota: Anteriormente, criávamos automaticamente a campanha pai se ela não fosse encontrada no banco de dados,
            // e definíamos seu status como 'ENABLED' independentemente do status real na API. Isso causava inconsistências
            // quando a campanha estava pausada na API, mas aparecia como ativa no banco de dados.
            // Agora, pulamos o grupo de anúncios se sua campanha pai não estiver no banco de dados, garantindo
            // que apenas grupos de anúncios pertencentes a campanhas sincronizadas (ENABLED ou PAUSED) sejam processados.
            if (!$dbCampaign) {
                $this->warn("Campanha não encontrada no banco de dados: {$campaign->getName()} (ID: {$campaignId}). Pulando grupo de anúncios...");
                // Pular este grupo de anúncios, pois sua campanha pai não está no banco de dados
                // Isso acontece quando a campanha está com status REMOVED ou não foi sincronizada por algum motivo
                continue;
            } else {
                // Log para mostrar o status da campanha no banco de dados
                $this->info("Campanha encontrada no banco de dados: {$dbCampaign->name} (ID: {$dbCampaign->google_campaign_id}), Status: {$dbCampaign->status}");
            }
            
            // Verificar se o grupo de anúncios já existe no banco de dados
            $existingAdGroup = AdGroup::where('google_ad_group_id', $adGroupId)->first();
            
            // Obter o status bruto e mapeado para logs
            $rawStatus = $adGroup->getStatus();
            $mappedStatus = AdGroupStatus::name($rawStatus);
            
            if ($existingAdGroup && !$force) {
                $this->line("Grupo de anúncios já existe: {$adGroup->getName()} (ID: {$adGroupId})");
                $this->info("Status do grupo de anúncios na API: Raw={$rawStatus}, Mapped={$mappedStatus}");
                $this->info("Status do grupo de anúncios no banco de dados: {$existingAdGroup->status}");
            } else {
                $this->info("Status do grupo de anúncios '{$adGroup->getName()}': Raw={$rawStatus}, Mapped={$mappedStatus}");
                
                // Criar ou atualizar o grupo de anúncios no banco de dados
                $adGroupData = [
                    'google_ad_group_id' => $adGroupId,
                    'resource_name' => $adGroup->getResourceName(),
                    'name' => $adGroup->getName(),
                    'status' => $mappedStatus,
                    'campaign_id' => $dbCampaign->id,
                ];

                // Log para debug
                $this->info("Dados do grupo de anúncios: " . json_encode($adGroupData));

                AdGroup::updateOrCreate(
                    ['google_ad_group_id' => $adGroupId],
                    $adGroupData
                );

                $this->line("Grupo de anúncios " . ($existingAdGroup ? "atualizado" : "criado") . ": {$adGroup->getName()} (ID: {$adGroupId})");
                $this->adGroupsCount++;
            }

            $adGroupsProcessed++;
        }

        $this->info("Processados {$adGroupsProcessed} grupos de anúncios.");
    }
}
