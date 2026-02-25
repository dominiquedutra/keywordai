<?php

namespace App\Console\Commands;

use App\Services\GoogleAdsQuotaService;
use Google\Ads\GoogleAds\Lib\V19\GoogleAdsClient;
use Google\Ads\GoogleAds\V19\Services\GoogleAdsRow;
use Google\Ads\GoogleAds\V19\Services\SearchGoogleAdsRequest;
use Google\ApiCore\ApiException;
use Google\ApiCore\ApiStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchCampaignsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'googleads:fetch-campaigns';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches and displays campaign names from Google Ads API';

    /**
     * The Google Ads API client.
     *
     * @var \Google\Ads\GoogleAds\Lib\V19\GoogleAdsClient
     */
    private $googleAdsClient;
    
    /**
     * O serviço de controle de cotas da API Google Ads.
     *
     * @var GoogleAdsQuotaService
     */
    private $quotaService;

    /**
     * Create a new command instance.
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
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Fetching campaign names...');

        try {
            // Obter o ID da conta do cliente do arquivo .ini
            // Como a biblioteca não tem um método getClientCustomerId(), precisamos ler do arquivo de configuração
            $clientCustomerId = config('app.client_customer_id', null);
            
            // Se não estiver definido no config/app.php, tentar ler diretamente do .ini
            if (empty($clientCustomerId)) {
                $iniPath = config('app.google_ads_php_path');
                if (file_exists($iniPath)) {
                    $iniConfig = parse_ini_file($iniPath, true);
                    $clientCustomerId = $iniConfig['GOOGLE_ADS']['clientCustomerId'] ?? null;
                }
            }
            
            // Se ainda não encontrou, usar o loginCustomerId como fallback
            if (empty($clientCustomerId)) {
                $clientCustomerId = $this->googleAdsClient->getLoginCustomerId();
                $this->info('Using Login Customer ID as fallback: ' . $clientCustomerId);
            } else {
                $this->info('Using Client Customer ID: ' . $clientCustomerId);
            }
            
            if (empty($clientCustomerId)) {
                $this->error('Customer ID is missing in configuration.');
                return Command::FAILURE;
            }

            // Verificar se há cota disponível antes de fazer a chamada à API
            if (!$this->quotaService->canMakeRequest()) {
                $this->error('Cota da API Google Ads excedida. Tente novamente mais tarde.');
                return Command::FAILURE;
            }
            
            // Criar o serviço GoogleAdsServiceClient
            $googleAdsServiceClient = $this->googleAdsClient->getGoogleAdsServiceClient();

            // Criar a consulta GAQL
            $query = 'SELECT campaign.id, campaign.name, campaign.status FROM campaign ORDER BY campaign.name';

            // Criar a requisição de busca
            $request = SearchGoogleAdsRequest::build($clientCustomerId, $query);

            // Executar a consulta
            $response = $googleAdsServiceClient->search($request);
            
            // Registra o uso da cota após uma chamada bem-sucedida
            $this->quotaService->recordRequest();

            $campaignCount = 0;
            // Iterar sobre os resultados
            foreach ($response->iterateAllElements() as $googleAdsRow) {
                /** @var GoogleAdsRow $googleAdsRow */
                $campaign = $googleAdsRow->getCampaign();
                // Converter o status para string de forma segura
                $status = $campaign->getStatus();
                $statusName = is_object($status) && method_exists($status, 'name') ? $status->name : (string)$status;
                
                try {
                    $this->line(sprintf('- [%s] %s (ID: %d)', 
                        $statusName, 
                        $campaign->getName(), 
                        $campaign->getId()
                    ));
                    $campaignCount++;
                } catch (\Exception $e) {
                    $this->error('Error displaying campaign: ' . $e->getMessage());
                    // Continue com o próximo item
                }
            }

            if ($campaignCount === 0) {
                $this->info('No campaigns found.');
            }

            $this->info('Finished fetching campaigns.');
            return Command::SUCCESS;

        } catch (ApiException $apiException) {
            $this->error('An API error occurred:');
            $this->error(sprintf('Status: %s', $apiException->getStatus()));
            $this->error(sprintf('Failure message: %s', $apiException->getBasicMessage()));
            // Logar detalhes adicionais
            Log::error('Google Ads API Exception:', [
                'status' => $apiException->getStatus(),
                'message' => $apiException->getBasicMessage(),
                'details' => $apiException->getMetadata(),
            ]);
            return Command::FAILURE;
        } catch (\Exception $exception) {
            $this->error('An unexpected error occurred:');
            $this->error($exception->getMessage());
            Log::error('Unexpected Exception fetching Google Ads Campaigns:', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }
    }
}
