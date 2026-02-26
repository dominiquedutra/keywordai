<?php

namespace App\Console\Commands;

use App\Models\SearchTerm;
use App\Services\GoogleAdsQuotaService;
use Google\Ads\GoogleAds\Lib\V20\GoogleAdsClient;
use Google\Ads\GoogleAds\V20\Enums\SearchTermTargetingStatusEnum\SearchTermTargetingStatus;
use Google\Ads\GoogleAds\V20\Enums\KeywordMatchTypeEnum\KeywordMatchType;
use Google\Ads\GoogleAds\V20\Services\GoogleAdsRow;
use Google\Ads\GoogleAds\V20\Services\SearchGoogleAdsRequest; // Mantido, mas usaremos searchStream
use Google\Ads\GoogleAds\V20\Services\SearchGoogleAdsStreamRequest; // Usaremos este
use Google\ApiCore\ApiException;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class FetchSearchTermsCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'googleads:fetch-search-terms 
        {--start-date= : The start date (YYYY-MM-DD), defaults to today if not provided} 
        {--end-date= : The end date (YYYY-MM-DD), defaults to today if not provided}
        {--only-none : Only fetch search terms with targeting status NONE (excluding ADDED/EXCLUDED)}
        {--save : Save search terms to the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches search terms with impressions for a given date range (or today if not specified) from the Google Ads API, optionally filtering by targeting status and saving to the database.';

    /**
     * The Google Ads API client.
     *
     * @var \Google\Ads\GoogleAds\Lib\V20\GoogleAdsClient
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
        // Obter as datas das opções
        $startDateOption = $this->option('start-date');
        $endDateOption = $this->option('end-date');

        // Usar a data atual (hoje) como padrão se as opções não forem fornecidas
        if (empty($startDateOption)) {
            $startDateOption = Carbon::today()->format('Y-m-d');
            $this->info("Usando data atual ({$startDateOption}) como data de início.");
        }
        
        if (empty($endDateOption)) {
            $endDateOption = Carbon::today()->format('Y-m-d');
            $this->info("Usando data atual ({$endDateOption}) como data de fim.");
        }

        try {
            $startDate = Carbon::parse($startDateOption);
            $endDate = Carbon::parse($endDateOption);
        } catch (\Exception $e) {
            $this->error('Invalid date format. Please use YYYY-MM-DD for both start and end dates.');
            return Command::FAILURE;
        }

        if ($endDate->isBefore($startDate)) {
            $this->error('End date cannot be before start date.');
            return Command::FAILURE;
        }

        // Formatar as datas após validação
        $startDateFormatted = $startDate->format('Y-m-d');
        $endDateFormatted = $endDate->format('Y-m-d');

        $this->info("Fetching search terms from {$startDateFormatted} to {$endDateFormatted}...");

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
                $this->error('Client Customer ID is missing in configuration (config/app.php or google_ads_php.ini).');
                return Command::FAILURE;
            }
            $this->info('Using Client Customer ID: ' . $clientCustomerId);

            // Obter a flag de filtro
            $onlyNone = $this->option('only-none');
            $saveToDatabase = $this->option('save');

            // Verificar se há cota disponível antes de fazer a chamada à API
            if (!$this->quotaService->canMakeRequest()) {
                $this->error('Cota da API Google Ads excedida. Tente novamente mais tarde.');
                return Command::FAILURE;
            }
            
            // Criar o serviço GoogleAdsServiceClient
            $googleAdsServiceClient = $this->googleAdsClient->getGoogleAdsServiceClient();

            // Construir a consulta GAQL com os campos válidos
            $query = "
                SELECT
                    search_term_view.search_term,
                    search_term_view.status,
                    segments.keyword.info.text, -- Texto da palavra-chave (caminho correto)
                    segments.keyword.info.match_type, -- Tipo de correspondência (caminho correto)
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
                WHERE segments.date BETWEEN '{$startDateFormatted}' AND '{$endDateFormatted}'
                  AND metrics.impressions > 0
            ";

            // Remover filtragem na query GAQL e informar que será feita no PHP
            if ($onlyNone) {
                $this->info('Filtering search terms: Only showing status NONE (via PHP filtering).'); 
            }

            $query .= " ORDER BY metrics.impressions DESC";


            // Criar a requisição de stream
            $request = new SearchGoogleAdsStreamRequest([
                'customer_id' => $clientCustomerId,
                'query' => $query
            ]);

            // Executar a consulta usando searchStream
            $stream = $googleAdsServiceClient->searchStream($request);
            
            // Registra o uso da cota após uma chamada bem-sucedida
            $this->quotaService->recordRequest();

            $termCount = 0;
            $savedCount = 0;
            $updatedCount = 0;
            
            // Ajustar o cabeçalho (adicionando Keyword e Match Type)
            $header = sprintf(
                "%-30s | %-30s | %-10s | %-11s | %-6s | %-7s | %-3s | %-10s | %-20s | %s",
                "Search Term", "Keyword", "Match Type", "Impressions", "Clicks", "Cost", "CTR", "Status", "Campaign", "Ad Group"
            );
            $this->info($header);
            $this->line(str_repeat('-', strlen($header) + 10)); // Ajustar tamanho do separador

            // Iterar sobre os resultados do stream
            foreach ($stream->iterateAllElements() as $googleAdsRow) {
                /** @var GoogleAdsRow $googleAdsRow */
                $searchTerm = null; // Inicializar fora do try
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
                        $keywordText = 'N/A (Campo não disponível)';
                        $matchType = 'N/A';
                    }
                    
                    // Filtrar no PHP se a flag estiver ativa
                    if ($onlyNone && $status !== 'NONE') {
                        continue; // Pula para o próximo termo se não for NONE e a flag estiver ativa
                    }

                    $impressions = $metrics->getImpressions();
                    $clicks = $metrics->getClicks();
                    $costMicros = $metrics->getCostMicros();
                    $cost = $costMicros / 1000000; // Convert micros
                    $ctr = $metrics->getCtr() * 100;
                    $campaignId = $campaign->getId();
                    $campaignName = $campaign->getName();
                    $adGroupId = $adGroup->getId();
                    $adGroupName = $adGroup->getName();
                    $segmentDate = $segments->getDate();

                    // Ajustar o formato da linha (incluindo Keyword e Match Type)
                    $outputLine = sprintf(
                        "%-30s | %-30s | %-10s | %-11d | %-6d | %-7.2f | %-3.2f | %-10s | %-20s | %s",
                        substr($searchTerm, 0, 30),
                        substr($keywordText, 0, 30),
                        $matchType,
                        $impressions,
                        $clicks,
                        $cost,
                        $ctr,
                        $status, // Status geral
                        substr($campaignName, 0, 20),
                        substr($adGroupName, 0, 20)
                    );
                    $this->line($outputLine);
                    $termCount++; // Incrementa o contador
                    
                    // Salvar no banco de dados se a flag estiver ativa
                    if ($saveToDatabase) {
                        try {
                            // Adicionar dump para depuração durante o teste
                            if (app()->runningUnitTests()) {
                                dump("Tentando salvar termo: {$searchTerm}");
                            }
                            
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
                                ]);
                                $updatedCount++;
                            } else {
                                // Criar um novo registro
                                SearchTerm::create([
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
                                ]);
                                $savedCount++;
                            }
                        } catch (\Exception $e) {
                            // Adicionar dump para depuração durante o teste
                            if (app()->runningUnitTests()) {
                                dump("Erro ao salvar termo: {$e->getMessage()}");
                            }
                            $this->error("Erro ao salvar termo no banco de dados: {$e->getMessage()}");
                            return Command::FAILURE;
                        }
                    }

                } catch (\Exception $e) {
                    $this->error('Error processing row: ' . $e->getMessage());
                    // Usar o $searchTerm inicializado como null se a exceção ocorreu antes de sua atribuição
                    $this->error("DEBUG: Exception occurred for search term: " . ($searchTerm ?? 'N/A')); // DEBUG ERRO
                    // Continue com o próximo item, não incrementa $termCount
                }
            } // Fim do foreach

            $this->line(str_repeat('-', strlen($header) + 10)); // Ajustar tamanho do separador
            if ($termCount === 0) {
                $this->info('No search terms found with impressions for the specified date range' . ($onlyNone ? ' and status NONE.' : '.'));
            } else {
                $this->info("Fetched {$termCount} search terms.");
                
                if ($saveToDatabase) {
                    $this->info("Database operations: {$savedCount} new terms saved, {$updatedCount} existing terms updated.");
                }
            }

            $this->info('Finished fetching search terms for the specified range.');
            return Command::SUCCESS;

        } catch (ApiException $apiException) {
            $this->error('An API error occurred:');
            $this->error(sprintf('Status: %s', $apiException->getStatus()));
            $this->error(sprintf('Failure message: %s', $apiException->getBasicMessage()));
            Log::error('Google Ads API Exception fetching search terms:', [
                'status' => $apiException->getStatus(),
                'message' => $apiException->getBasicMessage(),
                'details' => $apiException->getMetadata(),
                'query' => $query ?? 'N/A'
            ]);
            return Command::FAILURE;
        } catch (\Exception $exception) {
            $this->error('An unexpected error occurred:');
            $this->error($exception->getMessage());
            Log::error('Unexpected Exception fetching search terms:', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }
    }
}
