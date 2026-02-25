<?php

namespace App\Console\Commands;

use Google\Ads\GoogleAds\Lib\V19\GoogleAdsClient;
use Google\Ads\GoogleAds\V19\Enums\KeywordMatchTypeEnum\KeywordMatchType;
use Google\Ads\GoogleAds\V19\Resources\SharedCriterion;
use Google\Ads\GoogleAds\V19\Common\KeywordInfo;
use Google\ApiCore\ApiException; // Manter para possível validação futura, embora a lógica principal vá para o Job
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log; // Manter para logs do próprio comando, se necessário
use App\Jobs\AddNegativeKeywordJob; // Adicionar o Job

class GoogleAdsAddNegativeKeywordCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'googleads:add-negative-keyword 
        {term : O termo de pesquisa a ser negativado} 
        {list-id : O ID da lista de palavras-chave negativas (SharedSet ID)} 
        {match-type : O tipo de correspondência (broad, phrase, exact)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adiciona um termo como palavra-chave negativa a uma lista específica no Google Ads';

    /**
     * The Google Ads API client.
     *
     * @var \Google\Ads\GoogleAds\Lib\V19\GoogleAdsClient
     */
    private $googleAdsClient;

    /**
     * Create a new command instance.
     *
     * @param GoogleAdsClient $googleAdsClient
     * @return void
     */
    public function __construct(GoogleAdsClient $googleAdsClient)
    {
        parent::__construct();
        $this->googleAdsClient = $googleAdsClient;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        // Obter argumentos do comando
        $term = $this->argument('term');
        $listId = $this->argument('list-id');
        $matchTypeArg = strtolower($this->argument('match-type'));

        // Validar o tipo de correspondência
        $validMatchTypes = ['broad', 'phrase', 'exact'];
        if (!in_array($matchTypeArg, $validMatchTypes)) {
            $this->error("Tipo de correspondência inválido: '{$matchTypeArg}'. Use 'broad', 'phrase' ou 'exact'.");
            return Command::FAILURE;
        }

        // Validar se listId é numérico (simples validação)
        if (!is_numeric($listId)) {
             $this->error("O ID da lista ('list-id') deve ser numérico. Valor recebido: '{$listId}'.");
             return Command::FAILURE;
        }

        $this->info("Despachando Job para adicionar termo negativo: '{$term}' (tipo: {$matchTypeArg}) à lista ID: {$listId}");

        try {
            // Disparar o Job
            AddNegativeKeywordJob::dispatch($term, $listId, $matchTypeArg);

            $this->info("Job para adicionar palavra-chave negativa '{$term}' despachado para a fila com sucesso.");
            return Command::SUCCESS;

        } catch (\Exception $exception) {
            $this->error('Ocorreu um erro ao despachar o Job:');
            $this->error($exception->getMessage());
            Log::error('Exceção ao despachar AddNegativeKeywordJob:', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'term' => $term,
                'listId' => $listId,
                'matchType' => $matchTypeArg
            ]);
            return Command::FAILURE;
        }
    }
}
