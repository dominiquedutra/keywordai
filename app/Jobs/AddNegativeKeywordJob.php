<?php

namespace App\Jobs;

use App\Models\ActivityLog;
use App\Models\NegativeKeyword;
use App\Models\Setting;
use App\Services\GoogleAdsQuotaService;
use Google\Ads\GoogleAds\Lib\V20\GoogleAdsClient;
use Google\Ads\GoogleAds\V20\Enums\KeywordMatchTypeEnum\KeywordMatchType;
use Google\Ads\GoogleAds\V20\Resources\SharedCriterion;
use Google\Ads\GoogleAds\V20\Common\KeywordInfo;
use Google\Ads\GoogleAds\V20\Services\MutateSharedCriteriaRequest;
use Google\Ads\GoogleAds\V20\Services\SharedCriterionOperation;
use Google\ApiCore\ApiException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AddNegativeKeywordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    /**
     * O serviço de controle de cotas da API Google Ads.
     *
     * @var GoogleAdsQuotaService
     */
    protected GoogleAdsQuotaService $quotaService;

    /**
     * The search term to be added as a negative keyword.
     *
     * @var string
     */
    protected $term;

    /**
     * The ID of the negative keyword list (SharedSet ID).
     *
     * @var string
     */
    protected $listId;

    /**
     * The match type (e.g., 'phrase', 'exact', 'broad').
     *
     * @var string
     */
    protected $matchType;

    /**
     * The reason for adding this negative keyword.
     *
     * @var string|null
     */
    protected $reason;

    /**
     * The ID of the user who added this negative keyword.
     *
     * @var int|null
     */
    protected $userId;

    /**
     * Create a new job instance.
     *
     * @param string $term
     * @param string $listId
     * @param string $matchType
     * @param string|null $reason
     * @param int|null $userId
     */
    public function __construct(string $term, string $listId, string $matchType, ?string $reason = null, ?int $userId = null)
    {
        $this->term = $term;
        $this->listId = $listId;
        $this->matchType = strtolower($matchType); // Ensure lowercase for consistency
        $this->reason = $reason;
        $this->userId = $userId;
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
        $this->quotaService = $quotaService;
        
        // Verifica se há cota disponível antes de fazer a chamada à API
        if (!$this->quotaService->canMakeRequest()) {
            Log::warning("Cota da API Google Ads excedida. Liberando job de volta para a fila com atraso de 60 segundos.");
            $this->release(60); // Libera o job de volta para a fila com um atraso de 60 segundos
            return;
        }
        
        // Validar o tipo de correspondência (embora deva ser validado antes de despachar)
        $validMatchTypes = ['broad', 'phrase', 'exact'];
        if (!in_array($this->matchType, $validMatchTypes)) {
            Log::error("Tipo de correspondência inválido no Job: '{$this->matchType}'. Termo: '{$this->term}', Lista ID: '{$this->listId}'");
            // Poderia lançar uma exceção ou falhar o job aqui
            $this->fail(new \InvalidArgumentException("Tipo de correspondência inválido: '{$this->matchType}'"));
            return;
        }

        // Converter o tipo de correspondência para o enum da API
        $matchTypeEnum = null;
        switch ($this->matchType) {
            case 'broad':
                $matchTypeEnum = KeywordMatchType::BROAD;
                break;
            case 'phrase':
                $matchTypeEnum = KeywordMatchType::PHRASE;
                break;
            case 'exact':
                $matchTypeEnum = KeywordMatchType::EXACT;
                break;
        }

        Log::info("Iniciando Job para adicionar termo negativo: '{$this->term}' (tipo: {$this->matchType}) à lista ID: {$this->listId}", [
            'reason' => $this->reason,
            'userId' => $this->userId
        ]);

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
                Log::error('Client Customer ID não encontrado na configuração (config/app.php ou google_ads_php.ini) dentro do Job.');
                $this->fail(new \Exception('Client Customer ID não configurado.'));
                return;
            }
            Log::info('Job usando Client Customer ID: ' . $clientCustomerId);

            // Construir o nome do recurso da lista de palavras-chave negativas
            $sharedSetResourceName = "customers/{$clientCustomerId}/sharedSets/{$this->listId}";

            // Criar o KeywordInfo
            $keywordInfo = new KeywordInfo([
                'text' => $this->term,
                'match_type' => $matchTypeEnum
            ]);

            // Criar o SharedCriterion
            $sharedCriterion = new SharedCriterion([
                'shared_set' => $sharedSetResourceName,
                'keyword' => $keywordInfo
            ]);

            // Criar a operação
            $operation = new SharedCriterionOperation();
            $operation->setCreate($sharedCriterion);

            // Obter o serviço SharedCriterionService
            $sharedCriterionServiceClient = $googleAdsClient->getSharedCriterionServiceClient();

            // Criar a requisição de mutação
            $request = new MutateSharedCriteriaRequest([
                'customer_id' => $clientCustomerId,
                'operations' => [$operation]
            ]);

            // Executar a mutação
            $response = $sharedCriterionServiceClient->mutateSharedCriteria($request);

            // Verificar a resposta
            if (count($response->getResults()) > 0) {
                // Registra o uso da cota após uma chamada bem-sucedida
                $this->quotaService->recordRequest();
                
                $result = $response->getResults()[0];
                $resourceName = $result->getResourceName();
                
                Log::info("Palavra-chave negativa adicionada com sucesso pelo Job. Resource name: {$resourceName}", [
                    'term' => $this->term,
                    'listId' => $this->listId,
                    'matchType' => $this->matchType,
                    'reason' => $this->reason,
                    'userId' => $this->userId
                ]);
                
                // Criar registro na tabela negative_keywords
                try {
                    $negativeKeyword = NegativeKeyword::create([
                        'keyword' => $this->term,
                        'match_type' => $this->matchType,
                        'reason' => $this->reason,
                        'list_id' => $this->listId,
                        'resource_name' => $resourceName,
                        'created_by_id' => $this->userId,
                    ]);
                    
                    Log::info("Registro criado na tabela negative_keywords com ID: {$negativeKeyword->id}");

                    // Mark negatives summary as stale for regeneration
                    Setting::setValue('ai_negatives_summary_stale', '1', 'boolean');
                    
                    // Registrar a atividade na tabela activity_logs
                    if ($this->userId) {
                        ActivityLog::create([
                            'user_id' => $this->userId,
                            'action_type' => 'add_negative_keyword',
                            'entity_type' => 'negative_keyword',
                            'entity_id' => $negativeKeyword->id,
                            'details' => [
                                'keyword' => $this->term,
                                'match_type' => $this->matchType,
                                'reason' => $this->reason,
                                'list_id' => $this->listId,
                                'resource_name' => $resourceName,
                            ],
                        ]);
                        
                        Log::info("Atividade registrada na tabela activity_logs para o usuário ID: {$this->userId}");
                    } else {
                        Log::warning("Não foi possível registrar a atividade na tabela activity_logs porque o ID do usuário não foi fornecido.");
                    }
                } catch (\Exception $dbException) {
                    Log::error("Erro ao criar registro na tabela negative_keywords:", [
                        'message' => $dbException->getMessage(),
                        'trace' => $dbException->getTraceAsString(),
                        'term' => $this->term,
                        'listId' => $this->listId,
                        'matchType' => $this->matchType,
                        'reason' => $this->reason,
                        'userId' => $this->userId
                    ]);
                    // Não falhar o job aqui, pois a palavra-chave já foi adicionada com sucesso na API
                }
                
                // Buscar TODAS as instâncias do SearchTerm no banco de dados
                $searchTerms = \App\Models\SearchTerm::where('search_term', $this->term)->get();
                
                // Se encontrou instâncias do SearchTerm, despachar um job para cada uma
                if ($searchTerms->isNotEmpty()) {
                    Log::info("Encontradas {$searchTerms->count()} ocorrências do termo '{$this->term}' no banco de dados. Despachando jobs para sincronizar estatísticas.");
                    
                    foreach ($searchTerms as $searchTerm) {
                        Log::info("Despachando SyncSearchTermStatsJob para o termo '{$this->term}' na campanha {$searchTerm->campaign_id}, grupo de anúncios {$searchTerm->ad_group_id} (ID: {$searchTerm->id}) com delay de 10 segundos");
                        \App\Jobs\SyncSearchTermStatsJob::dispatch($searchTerm)->delay(now()->addSeconds(10))->onQueue('default');
                    }
                } else {
                    Log::warning("Nenhuma ocorrência do termo '{$this->term}' encontrada no banco de dados. Não foi possível despachar SyncSearchTermStatsJob.");
                }
            } else {
                Log::error("Nenhum resultado retornado pela API ao adicionar palavra-chave negativa pelo Job.", [
                    'term' => $this->term,
                    'listId' => $this->listId,
                    'matchType' => $this->matchType,
                    'reason' => $this->reason,
                    'userId' => $this->userId
                ]);
                $this->fail(new \Exception('Nenhum resultado retornado pela API.'));
            }

        } catch (ApiException $apiException) {
            Log::error('Google Ads API Exception no Job ao adicionar palavra-chave negativa:', [
                'status' => $apiException->getStatus(),
                'message' => $apiException->getBasicMessage(),
                'details' => $apiException->getMetadata(),
                'term' => $this->term,
                'listId' => $this->listId,
                'matchType' => $this->matchType,
                'reason' => $this->reason,
                'userId' => $this->userId
            ]);
            // Verificar se é um erro de palavra-chave duplicada e talvez não falhar o job
            $isDuplicate = false;
            $metadata = $apiException->getMetadata();
            if ($metadata && isset($metadata['google.ads.googleads.v19.errors.googleadsfailure-bin'])) {
                 // Uma análise mais profunda da estrutura do erro seria necessária aqui para confirmar 100%
                 // Por agora, vamos logar como erro mas não necessariamente falhar o job se for duplicado
                 Log::warning('Possível erro de palavra-chave duplicada detectado na API.');
                 // $isDuplicate = true; // Lógica para detectar duplicata
            }
            
            // Falhar o job se não for um erro conhecido/esperado como duplicata
            // if (!$isDuplicate) {
                 $this->fail($apiException);
            // }

        } catch (\Exception $exception) {
            Log::error('Exceção inesperada no Job ao adicionar palavra-chave negativa:', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'term' => $this->term,
                'listId' => $this->listId,
                'matchType' => $this->matchType,
                'reason' => $this->reason,
                'userId' => $this->userId
            ]);
            $this->fail($exception);
        }
    }
}
