# Batch Stats Sync — Como funciona

## O problema

O comando `keywordai:sync-all-active-stats` sincroniza as estatísticas (impressions, clicks, cost, CTR) de todos os termos de pesquisa ativos com o Google Ads.

**Antes:** o comando criava **1 job por termo**. Com ~11.000 termos ativos, isso significava:

- 11.000 jobs na fila
- 11.000 chamadas individuais à API do Google Ads
- Quase esgotava a cota diária de 14.000 requests

**Depois:** o comando cria **1 job** que faz **1 chamada** à API e atualiza todos os termos numa única passada.

---

## O que são "termos ativos"?

O filtro usado é:

```php
SearchTerm::where('status', '!=', 'EXCLUDED')
```

Isso inclui todos os termos com qualquer status **exceto** `EXCLUDED` (termos que foram negativados). Na prática, inclui `ADDED`, `NONE`, `UNKNOWN` e qualquer outro status do enum `SearchTermTargetingStatus` do Google Ads.

---

## O truque da agregação GAQL

A chave da otimização está na construção da query GAQL (Google Ads Query Language):

```sql
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
WHERE segments.date BETWEEN '2024-01-01' AND '2026-02-27'
  AND metrics.impressions > 0
ORDER BY metrics.cost_micros DESC
```

### Por que isso funciona?

O Google Ads usa `segments.date` para controlar o nível de granularidade dos dados:

| `segments.date` no SELECT? | `segments.date` no WHERE? | Resultado |
|---|---|---|
| Sim | Sim | 1 linha por (termo, campanha, ad_group, **dia**) |
| **Nao** | **Sim** | **1 linha por (termo, campanha, ad_group) com metricas agregadas** |

Ao **omitir** `segments.date` do `SELECT` mas **manter** no `WHERE`, o Google Ads retorna automaticamente as metricas **somadas** no periodo inteiro. Exatamente o que o job antigo fazia por termo — mas agora tudo de uma vez.

### Diagrama do fluxo

```
ANTES (N chamadas):
  Comando
    ├── Job termo_1 → API call → update DB
    ├── Job termo_2 → API call → update DB
    ├── Job termo_3 → API call → update DB
    ├── ...
    └── Job termo_11000 → API call → update DB

DEPOIS (1 chamada):
  Comando
    └── BatchSyncJob → 1 API call (stream) → iterate → update DB para cada termo
```

---

## Fluxo detalhado do BatchSyncSearchTermStatsJob

### 1. Verificacao de cota

Antes de qualquer chamada, o job consulta o `GoogleAdsQuotaService`:

```php
if (!$quotaService->canMakeRequest()) {
    $this->release(60); // volta pra fila, tenta de novo em 60s
    return;
}
```

### 2. Resolucao do Customer ID

O job resolve o `clientCustomerId` na mesma ordem de prioridade que os outros jobs:

1. `config('app.client_customer_id')` (env var)
2. Fallback: leitura do `google_ads_php.ini`

### 3. Chamada streaming

Usa `searchStream` em vez de `search` (paginado). A resposta chega como stream gRPC — o PHP processa linha a linha sem carregar tudo em memoria:

```php
$stream = $googleAdsServiceClient->searchStream($request);

foreach ($stream->iterateAllElements() as $googleAdsRow) {
    // processa 1 termo por vez
}
```

Isso e importante para o VPS de 1GB em producao: mesmo com 11.000 termos, o consumo de memoria permanece baixo.

### 4. Lookup e update por chave unica

Para cada linha retornada pela API, o job busca o `SearchTerm` existente no banco por sua chave unica natural:

```php
$existingTerm = SearchTerm::where('campaign_id', $campaignId)
    ->where('ad_group_id', $adGroupId)
    ->where('search_term', $searchTermText)
    ->first();
```

Se encontra, atualiza:

```php
$existingTerm->update([
    'impressions'          => $impressions,
    'clicks'               => $clicks,
    'cost_micros'          => $costMicros,
    'ctr'                  => $ctr,
    'status'               => $status,
    'campaign_name'        => $campaign->getName(),
    'ad_group_name'        => $adGroup->getName(),
    'statistics_synced_at' => now(),
]);
```

Se **nao** encontra (termo existe no Google Ads mas nao no nosso banco), apenas incrementa o contador `notFoundCount`. Novos termos **nao** sao criados aqui — isso e responsabilidade do `SyncSearchTermsForDateJob`.

### 5. Log de progresso

A cada 500 termos processados, grava um log:

```
BatchSyncSearchTermStatsJob: Progresso... {"processed":500,"updated":487,"not_found":13}
```

### 6. Resultado final

Ao terminar o stream:

```
BatchSyncSearchTermStatsJob: Sincronizacao em lote concluida. {"updated":10832,"not_found_in_db":215,"errors":0}
```

---

## Tratamento de erros e retries

| Cenario | Comportamento |
|---|---|
| Cota excedida (pre-check) | `release(60)` — volta pra fila em 60s |
| `ApiException` do Google Ads | `fail()` — marca como falha, retry automatico |
| Excecao generica | `fail()` — marca como falha, retry automatico |
| Erro em 1 termo individual | Log do erro, continua com o proximo (nao falha o job inteiro) |

Configuracao de retries:

```php
public $tries = 3;
public $backoff = [60, 300, 600]; // 1min, 5min, 10min
```

---

## Relacao com outros jobs

```
BatchSyncSearchTermStatsJob     ← NOVO: atualiza stats em massa (1 API call)
SyncSearchTermStatsJob          ← MANTIDO: atualiza 1 termo (usado pelo AJAX de refresh individual)
SyncSearchTermsForDateJob       ← EXISTENTE: sincroniza termos por data (cria novos termos)
```

O `SyncSearchTermStatsJob` continua existindo e sendo usado para:
- Refresh individual de stats via UI (botao "atualizar" em um termo)
- `SearchTermObserver` ao criar um novo termo
- Apos adicionar keyword/negativar termo (`AddKeywordToAdGroupJob`, `AddNegativeKeywordJob`)

---

## Uso

```bash
# Ver o que seria feito
php artisan keywordai:sync-all-active-stats --dry-run

# Executar na fila default
php artisan keywordai:sync-all-active-stats

# Executar numa fila especifica
php artisan keywordai:sync-all-active-stats --queue=bulk
```
