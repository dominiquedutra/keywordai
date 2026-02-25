<?php

namespace App\Console\Commands;

use App\Services\GoogleAdsQuotaService;
use Google\Ads\GoogleAds\Lib\V19\GoogleAdsClient;
use Google\Ads\GoogleAds\V19\Enums\SearchTermTargetingStatusEnum\SearchTermTargetingStatus;
use Google\Ads\GoogleAds\V19\Services\GoogleAdsRow;
use Google\Ads\GoogleAds\V19\Services\SearchGoogleAdsStreamRequest;
use Google\ApiCore\ApiException;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class GetSearchTermStatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'googleads:get-search-term-stats 
        {term : O termo de pesquisa a ser consultado} 
        {--start-date= : Data de início (YYYY-MM-DD)} 
        {--end-date= : Data de fim (YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Obtém estatísticas agregadas (custo, impressões, cliques, conversões, valor) para um termo de pesquisa específico';

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
        // Obter o termo de pesquisa
        $term = $this->argument('term');
        $this->info("Buscando estatísticas para o termo: '{$term}'");

        // Obter e validar as datas
        $startDateOption = $this->option('start-date');
        $endDateOption = $this->option('end-date');

        // Se nenhuma data for fornecida, usar os últimos 12 meses
        if (empty($startDateOption) && empty($endDateOption)) {
            $endDate = Carbon::now();
            $startDate = Carbon::now()->subMonths(12);
        } 
        // Se apenas uma data for fornecida, retornar erro
        elseif (empty($startDateOption) || empty($endDateOption)) {
            $this->error('Você deve fornecer ambas as datas (--start-date e --end-date) ou nenhuma (para usar os últimos 12 meses).');
            return Command::FAILURE;
        } 
        // Se ambas as datas forem fornecidas, validar o formato
        else {
            try {
                $startDate = Carbon::parse($startDateOption);
                $endDate = Carbon::parse($endDateOption);
            } catch (\Exception $e) {
                $this->error('Formato de data inválido. Use YYYY-MM-DD para ambas as datas.');
                return Command::FAILURE;
            }

            if ($endDate->isBefore($startDate)) {
                $this->error('A data final não pode ser anterior à data inicial.');
                return Command::FAILURE;
            }
        }

        // Formatar as datas após validação
        $startDateFormatted = $startDate->format('Y-m-d');
        $endDateFormatted = $endDate->format('Y-m-d');

        $this->info("Período de consulta: {$startDateFormatted} até {$endDateFormatted}");

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
                $this->error('Client Customer ID não encontrado na configuração (config/app.php ou google_ads_php.ini).');
                return Command::FAILURE;
            }
            $this->info('Usando Client Customer ID: ' . $clientCustomerId);

            // Verificar se há cota disponível antes de fazer a chamada à API
            if (!$this->quotaService->canMakeRequest()) {
                $this->error('Cota da API Google Ads excedida. Tente novamente mais tarde.');
                return Command::FAILURE;
            }
            
            // Criar o serviço GoogleAdsServiceClient
            $googleAdsServiceClient = $this->googleAdsClient->getGoogleAdsServiceClient();

            // Construir a consulta GAQL
            $query = "
                SELECT
                    search_term_view.search_term,
                    search_term_view.status,
                    metrics.cost_micros,
                    metrics.impressions,
                    metrics.clicks,
                    metrics.conversions,
                    metrics.conversions_value,
                    segments.date
                FROM search_term_view
                WHERE search_term_view.search_term = '{$term}'
                  AND segments.date BETWEEN '{$startDateFormatted}' AND '{$endDateFormatted}'
                ORDER BY segments.date DESC
            ";

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
            $totalConversions = 0;
            $totalConversionsValue = 0;
            $latestStatus = null;
            $rowCount = 0;

            // Iterar sobre os resultados do stream
            foreach ($stream->iterateAllElements() as $googleAdsRow) {
                /** @var GoogleAdsRow $googleAdsRow */
                try {
                    $searchTermView = $googleAdsRow->getSearchTermView();
                    $metrics = $googleAdsRow->getMetrics();
                    
                    // Agregar métricas
                    $totalCostMicros += $metrics->getCostMicros();
                    $totalImpressions += $metrics->getImpressions();
                    $totalClicks += $metrics->getClicks();
                    $totalConversions += $metrics->getConversions();
                    $totalConversionsValue += $metrics->getConversionsValue();
                    
                    // Capturar o status mais recente (primeira linha devido ao ORDER BY DESC)
                    if ($latestStatus === null) {
                        $statusEnum = $searchTermView->getStatus();
                        $latestStatus = is_int($statusEnum) ? SearchTermTargetingStatus::name($statusEnum) : 'UNKNOWN';
                    }
                    
                    $rowCount++;
                } catch (\Exception $e) {
                    $this->error('Erro ao processar linha: ' . $e->getMessage());
                    // Continuar com a próxima linha
                }
            }

            // Verificar se encontramos algum dado
            if ($rowCount === 0) {
                $this->info("Nenhum dado encontrado para o termo '{$term}' no período especificado.");
                return Command::SUCCESS;
            }

            // Converter e formatar os resultados
            $totalCost = $totalCostMicros / 1000000; // Converter micros para unidades monetárias
            $ctr = $totalImpressions > 0 ? ($totalClicks / $totalImpressions) * 100 : 0;
            $conversionRate = $totalClicks > 0 ? ($totalConversions / $totalClicks) * 100 : 0;
            $costPerConversion = $totalConversions > 0 ? $totalCost / $totalConversions : 0;

            // Exibir os resultados
            $this->line("\n" . str_repeat('=', 50));
            $this->info("ESTATÍSTICAS PARA O TERMO: '{$term}'");
            $this->line(str_repeat('-', 50));
            $this->line("Período: {$startDateFormatted} até {$endDateFormatted}");
            $this->line("Status Atual: {$latestStatus}");
            $this->line(str_repeat('-', 50));
            $this->line("Impressões: " . number_format($totalImpressions, 0, ',', '.'));
            $this->line("Cliques: " . number_format($totalClicks, 0, ',', '.'));
            $this->line("CTR: " . number_format($ctr, 2, ',', '.') . "%");
            $this->line("Custo Total: R$ " . number_format($totalCost, 2, ',', '.'));
            $this->line("Custo por Clique: R$ " . ($totalClicks > 0 ? number_format($totalCost / $totalClicks, 2, ',', '.') : '0,00'));
            $this->line(str_repeat('-', 50));
            $this->line("Conversões: " . number_format($totalConversions, 2, ',', '.'));
            $this->line("Taxa de Conversão: " . number_format($conversionRate, 2, ',', '.') . "%");
            $this->line("Custo por Conversão: R$ " . number_format($costPerConversion, 2, ',', '.'));
            $this->line("Valor Total de Conversões: R$ " . number_format($totalConversionsValue, 2, ',', '.'));
            $this->line("ROAS: " . ($totalCost > 0 ? number_format(($totalConversionsValue / $totalCost) * 100, 2, ',', '.') . "%" : 'N/A'));
            $this->line(str_repeat('=', 50) . "\n");

            $this->info("Estatísticas agregadas com sucesso para '{$term}'.");
            return Command::SUCCESS;

        } catch (ApiException $apiException) {
            $this->error('Ocorreu um erro na API:');
            $this->error(sprintf('Status: %s', $apiException->getStatus()));
            $this->error(sprintf('Mensagem de falha: %s', $apiException->getBasicMessage()));
            Log::error('Google Ads API Exception ao buscar estatísticas do termo:', [
                'status' => $apiException->getStatus(),
                'message' => $apiException->getBasicMessage(),
                'details' => $apiException->getMetadata(),
                'term' => $term,
                'query' => $query ?? 'N/A'
            ]);
            return Command::FAILURE;
        } catch (\Exception $exception) {
            $this->error('Ocorreu um erro inesperado:');
            $this->error($exception->getMessage());
            Log::error('Exceção inesperada ao buscar estatísticas do termo:', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'term' => $term
            ]);
            return Command::FAILURE;
        }
    }
}
