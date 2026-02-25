<?php

namespace App\Jobs;

use App\Models\ActivityLog;
use App\Models\AdGroup;
use App\Services\GoogleAdsQuotaService;
use Google\Ads\GoogleAds\Lib\V19\GoogleAdsClient;
use Google\Ads\GoogleAds\V19\Common\KeywordInfo; // Correct namespace
use Google\Ads\GoogleAds\V19\Enums\KeywordMatchTypeEnum\KeywordMatchType;
use Google\Ads\GoogleAds\V19\Resources\AdGroupCriterion;
// use Google\Ads\GoogleAds\V19\Resources\KeywordInfo; // Remove or comment out incorrect one if it existed
use Google\Ads\GoogleAds\V19\Services\AdGroupCriterionOperation;
use Google\Ads\GoogleAds\V19\Services\AdGroupCriterionServiceClient; // Add this missing use statement
use Google\Ads\GoogleAds\V19\Services\MutateAdGroupCriteriaRequest;
use Google\ApiCore\ApiException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AddKeywordToAdGroupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $searchTerm;
    protected int $adGroupId;
    protected string $matchType;
    protected string $clientCustomerId;
    protected ?int $userId;
    protected GoogleAdsQuotaService $quotaService;

    /**
     * Create a new job instance.
     *
     * @param string $searchTerm The search term to add as a keyword.
     * @param int $adGroupId The ID of the ad group to add the keyword to.
     * @param string $matchType The match type (e.g., 'EXACT', 'PHRASE', 'BROAD').
     * @param string $clientCustomerId The target client customer ID.
     * @param int|null $userId The ID of the user who added this keyword.
     */
    public function __construct(string $searchTerm, int $adGroupId, string $matchType, string $clientCustomerId, ?int $userId = null)
    {
        $this->searchTerm = $searchTerm;
        $this->adGroupId = $adGroupId;
        $this->matchType = strtoupper($matchType); // Ensure match type is uppercase
        $this->clientCustomerId = $clientCustomerId;
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
        
        // Validate match type
        $validMatchTypes = [
            KeywordMatchType::EXACT,
            KeywordMatchType::PHRASE,
            KeywordMatchType::BROAD,
        ];
        $matchTypeEnum = KeywordMatchType::value($this->matchType);

        if (!in_array($matchTypeEnum, $validMatchTypes, true)) {
            Log::error("AddKeywordToAdGroupJob failed: Invalid match type '{$this->matchType}' provided for term '{$this->searchTerm}' in Ad Group ID {$this->adGroupId}.");
            $this->fail(new \InvalidArgumentException("Invalid match type '{$this->matchType}'"));
            return;
        }

        // Create the keyword info object.
        $keywordInfo = new KeywordInfo([
            'text' => $this->searchTerm,
            'match_type' => $matchTypeEnum
        ]);

        // Construct the Ad Group Criterion object.
        $adGroupResourceName = "customers/{$this->clientCustomerId}/adGroups/{$this->adGroupId}"; // Build resource name directly
        Log::debug("Constructed Ad Group Resource Name: {$adGroupResourceName}"); // Add log for verification

        $adGroupCriterion = new AdGroupCriterion([
            // Set the ad group resource name directly.
            'ad_group' => $adGroupResourceName,
            // Set the keyword info.
            'keyword' => $keywordInfo,
            // Optional: Set status to PAUSED initially if needed. Default is ENABLED.
            // 'status' => AdGroupCriterionStatusEnum\AdGroupCriterionStatus::PAUSED
        ]);
        // Note: We are not setting cpc_bid_micros to use the ad group's default bidding strategy.

        // Create the operation.
        $operation = new AdGroupCriterionOperation();
        $operation->setCreate($adGroupCriterion);

        // Get the AdGroupCriterionServiceClient.
        $adGroupCriterionServiceClient = $googleAdsClient->getAdGroupCriterionServiceClient();

        try {
            // Issue the mutate request.
            $response = $adGroupCriterionServiceClient->mutateAdGroupCriteria(
                MutateAdGroupCriteriaRequest::build($this->clientCustomerId, [$operation])
            );

            // Registra o uso da cota após uma chamada bem-sucedida
            $this->quotaService->recordRequest();

            $addedCriterionResourceName = $response->getResults()[0]->getResourceName();
            Log::info("Successfully added keyword '{$this->searchTerm}' ({$this->matchType}) to Ad Group ID {$this->adGroupId}. Criterion Resource Name: {$addedCriterionResourceName}", [
                'userId' => $this->userId
            ]);

            // Buscar informações do grupo de anúncios para o log de atividade
            $adGroupInfo = $this->getAdGroupInfo();
            
            // Registrar a atividade na tabela activity_logs
            if ($this->userId) {
                try {
                    ActivityLog::create([
                        'user_id' => $this->userId,
                        'action_type' => 'add_keyword',
                        'entity_type' => 'keyword',
                        'ad_group_id' => $this->adGroupId,
                        'ad_group_name' => $adGroupInfo['ad_group_name'] ?? null,
                        'campaign_id' => $adGroupInfo['campaign_id'] ?? null,
                        'campaign_name' => $adGroupInfo['campaign_name'] ?? null,
                        'details' => [
                            'keyword' => $this->searchTerm,
                            'match_type' => $this->matchType,
                            'resource_name' => $addedCriterionResourceName,
                        ],
                    ]);
                    
                    Log::info("Atividade registrada na tabela activity_logs para o usuário ID: {$this->userId}");
                } catch (\Exception $logException) {
                    Log::error("Erro ao registrar atividade na tabela activity_logs:", [
                        'message' => $logException->getMessage(),
                        'trace' => $logException->getTraceAsString(),
                        'userId' => $this->userId,
                        'searchTerm' => $this->searchTerm,
                        'adGroupId' => $this->adGroupId,
                        'matchType' => $this->matchType
                    ]);
                    // Não falhar o job aqui, pois a palavra-chave já foi adicionada com sucesso na API
                }
            } else {
                Log::warning("Não foi possível registrar a atividade na tabela activity_logs porque o ID do usuário não foi fornecido.");
            }

            // Buscar TODAS as instâncias do SearchTerm no banco de dados
            $searchTerms = \App\Models\SearchTerm::where('search_term', $this->searchTerm)->get();
            
            // Se encontrou instâncias do SearchTerm, despachar um job para cada uma
            if ($searchTerms->isNotEmpty()) {
                Log::info("Encontradas {$searchTerms->count()} ocorrências do termo '{$this->searchTerm}' no banco de dados. Despachando jobs para sincronizar estatísticas.");
                
                foreach ($searchTerms as $searchTerm) {
                    Log::info("Despachando SyncSearchTermStatsJob para o termo '{$this->searchTerm}' na campanha {$searchTerm->campaign_id}, grupo de anúncios {$searchTerm->ad_group_id} (ID: {$searchTerm->id})");
                    \App\Jobs\SyncSearchTermStatsJob::dispatch($searchTerm)->onQueue('default');
                }
            } else {
                Log::warning("Nenhuma ocorrência do termo '{$this->searchTerm}' encontrada no banco de dados. Não foi possível despachar SyncSearchTermStatsJob.");
            }

        } catch (ApiException $apiException) {
            $errorMessage = "Failed to add keyword '{$this->searchTerm}' ({$this->matchType}) to Ad Group ID {$this->adGroupId}. API Error: {$apiException->getMessage()}";
            Log::error($errorMessage, [
                'details' => $apiException->getMetadata(),
                'userId' => $this->userId
            ]);
            // Check for specific duplicate error (adjust error code/message as needed based on API docs)
            // Example check (may need refinement):
            if (str_contains($apiException->getBasicMessage(), 'CriterionError.DUPLICATE_KEYWORD')) {
                 Log::warning("Keyword '{$this->searchTerm}' ({$this->matchType}) already exists in Ad Group ID {$this->adGroupId}.");
                 // Optionally, don't fail the job if it's just a duplicate.
                 return; // Exit gracefully
            }
            $this->fail($apiException); // Fail the job for other API errors
        } catch (\Exception $exception) {
            $errorMessage = "Failed to add keyword '{$this->searchTerm}' ({$this->matchType}) to Ad Group ID {$this->adGroupId}. Unexpected Error: {$exception->getMessage()}";
            Log::error($errorMessage, [
                'userId' => $this->userId
            ]);
            $this->fail($exception); // Fail the job for unexpected errors
        }
    }

    /**
     * Buscar informações do grupo de anúncios para o log de atividade.
     *
     * @return array
     */
    private function getAdGroupInfo(): array
    {
        try {
            // Buscar o grupo de anúncios no banco de dados local
            $adGroup = AdGroup::with('campaign')
                ->where('google_ad_group_id', $this->adGroupId)
                ->first();

            if ($adGroup) {
                return [
                    'ad_group_name' => $adGroup->name,
                    'campaign_id' => $adGroup->campaign->google_campaign_id ?? null,
                    'campaign_name' => $adGroup->campaign->name ?? null,
                ];
            }
            
            // Se não encontrou no banco de dados, retornar array vazio
            Log::warning("Não foi possível encontrar informações do grupo de anúncios ID: {$this->adGroupId} no banco de dados local.");
            return [];
        } catch (\Exception $exception) {
            Log::error("Erro ao buscar informações do grupo de anúncios:", [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'adGroupId' => $this->adGroupId
            ]);
            return [];
        }
    }
}
