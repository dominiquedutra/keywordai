@extends('docs.layout')

@section('title', 'Batch Stats Sync')

@push('styles')
<style>
    .flow-node {
        @apply border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800;
    }
    .flow-node-highlight {
        @apply border-2 border-indigo-400 dark:border-indigo-500 rounded-lg px-4 py-2 text-sm font-medium text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/30;
    }
    .flow-arrow {
        @apply text-gray-400 dark:text-gray-500 text-lg leading-none;
    }
</style>
@endpush

@section('sidebar-extra')
    <h5 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider">Nesta Pagina</h5>
    <ul class="space-y-1">
        <li><a href="#problema" class="nav-link">O Problema</a></li>
        <li><a href="#termos-ativos" class="nav-link">Termos Ativos</a></li>
        <li><a href="#agregacao-gaql" class="nav-link">Agregacao GAQL</a></li>
        <li><a href="#fluxo-detalhado" class="nav-link">Fluxo Detalhado</a></li>
        <li><a href="#tratamento-erros" class="nav-link">Tratamento de Erros</a></li>
        <li><a href="#relacao-jobs" class="nav-link">Relacao com Outros Jobs</a></li>
        <li><a href="#uso" class="nav-link">Uso</a></li>
    </ul>
@endsection

@section('docs-content')
    <!-- Title -->
    <section class="mb-12">
        <div class="mb-6">
            <a href="{{ route('docs.sistema.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 inline-flex items-center">
                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Voltar ao indice
            </a>
        </div>

        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Batch Stats Sync — Como funciona</h1>
        <p class="text-lg text-gray-600 dark:text-gray-300">
            O comando <code class="text-sm font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">keywordai:sync-all-active-stats</code>
            sincroniza as estatisticas (impressions, clicks, cost, CTR) de todos os termos de pesquisa ativos com o Google Ads.
        </p>
    </section>

    <!-- O Problema -->
    <section id="problema" class="mb-12">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">O Problema</h2>

        <div class="grid md:grid-cols-2 gap-6 mb-6">
            <!-- Antes -->
            <div class="border border-red-200 dark:border-red-800 rounded-lg p-5 bg-red-50/50 dark:bg-red-900/10">
                <h3 class="text-lg font-semibold text-red-700 dark:text-red-400 mb-3">Antes</h3>
                <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">O comando criava <strong>1 job por termo</strong>. Com ~11.000 termos ativos:</p>
                <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1.5">
                    <li class="flex items-start">
                        <svg class="h-4 w-4 text-red-500 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        11.000 jobs na fila
                    </li>
                    <li class="flex items-start">
                        <svg class="h-4 w-4 text-red-500 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        11.000 chamadas individuais a API
                    </li>
                    <li class="flex items-start">
                        <svg class="h-4 w-4 text-red-500 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Quase esgotava a cota diaria de 14.000 requests
                    </li>
                </ul>
            </div>

            <!-- Depois -->
            <div class="border border-green-200 dark:border-green-800 rounded-lg p-5 bg-green-50/50 dark:bg-green-900/10">
                <h3 class="text-lg font-semibold text-green-700 dark:text-green-400 mb-3">Depois</h3>
                <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">O comando cria <strong>1 job</strong> que faz <strong>1 chamada</strong> a API:</p>
                <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1.5">
                    <li class="flex items-start">
                        <svg class="h-4 w-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        1 unico job na fila
                    </li>
                    <li class="flex items-start">
                        <svg class="h-4 w-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        1 chamada streaming a API
                    </li>
                    <li class="flex items-start">
                        <svg class="h-4 w-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Todos os termos atualizados numa unica passada
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Termos Ativos -->
    <section id="termos-ativos" class="mb-12">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">O que sao "termos ativos"?</h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">O filtro usado e:</p>
        <div class="relative">
            <pre class="code-block"><code>SearchTerm::where('status', '!=', 'EXCLUDED')</code></pre>
            <button onclick="copyCode(this)" class="copy-btn">Copiar</button>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mt-4">
            Isso inclui todos os termos com qualquer status <strong>exceto</strong> <code class="text-sm font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">EXCLUDED</code>
            (termos que foram negativados). Na pratica, inclui <code class="text-sm font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">ADDED</code>,
            <code class="text-sm font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">NONE</code>,
            <code class="text-sm font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">UNKNOWN</code>
            e qualquer outro status do enum <code class="text-sm font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">SearchTermTargetingStatus</code> do Google Ads.
        </p>
    </section>

    <!-- Agregacao GAQL -->
    <section id="agregacao-gaql" class="mb-12">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">O truque da agregacao GAQL</h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A chave da otimizacao esta na construcao da query GAQL (Google Ads Query Language):</p>

        <div class="relative mb-6">
            <pre class="code-block"><code>SELECT
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
ORDER BY metrics.cost_micros DESC</code></pre>
            <button onclick="copyCode(this)" class="copy-btn">Copiar</button>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Por que isso funciona?</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            O Google Ads usa <code class="text-sm font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">segments.date</code> para controlar o nivel de granularidade dos dados:
        </p>

        <div class="overflow-x-auto mb-6">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">segments.date no SELECT?</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">segments.date no WHERE?</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Resultado</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">Sim</td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">Sim</td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">1 linha por (termo, campanha, ad_group, <strong>dia</strong>)</td>
                    </tr>
                    <tr class="bg-indigo-50/50 dark:bg-indigo-900/10">
                        <td class="px-6 py-4 text-sm font-medium text-indigo-700 dark:text-indigo-300">Nao</td>
                        <td class="px-6 py-4 text-sm font-medium text-indigo-700 dark:text-indigo-300">Sim</td>
                        <td class="px-6 py-4 text-sm font-medium text-indigo-700 dark:text-indigo-300">1 linha por (termo, campanha, ad_group) com metricas <strong>agregadas</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg p-4 mb-6">
            <p class="text-sm text-indigo-800 dark:text-indigo-300">
                Ao <strong>omitir</strong> <code class="font-mono">segments.date</code> do <code class="font-mono">SELECT</code> mas <strong>manter</strong> no <code class="font-mono">WHERE</code>,
                o Google Ads retorna automaticamente as metricas <strong>somadas</strong> no periodo inteiro. Exatamente o que o job antigo fazia por termo — mas agora tudo de uma vez.
            </p>
        </div>

        <!-- Flow Diagram -->
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Diagrama do fluxo</h3>

        <div class="grid md:grid-cols-2 gap-6">
            <!-- Antes -->
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-5">
                <h4 class="text-sm font-semibold text-red-600 dark:text-red-400 uppercase tracking-wider mb-4">Antes (N chamadas)</h4>
                <div class="space-y-2">
                    <div class="flow-node text-center">Comando</div>
                    <div class="text-center flow-arrow">|</div>
                    <div class="space-y-1.5 pl-6 border-l-2 border-gray-300 dark:border-gray-600 ml-[calc(50%-1px)]">
                        <div class="flow-node -ml-6">Job termo_1 → API call → update DB</div>
                        <div class="flow-node -ml-6">Job termo_2 → API call → update DB</div>
                        <div class="flow-node -ml-6">Job termo_3 → API call → update DB</div>
                        <div class="text-center text-gray-400 dark:text-gray-500 text-sm -ml-6">...</div>
                        <div class="flow-node -ml-6">Job termo_11000 → API call → update DB</div>
                    </div>
                </div>
            </div>

            <!-- Depois -->
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-5">
                <h4 class="text-sm font-semibold text-green-600 dark:text-green-400 uppercase tracking-wider mb-4">Depois (1 chamada)</h4>
                <div class="space-y-2">
                    <div class="flow-node text-center">Comando</div>
                    <div class="text-center flow-arrow">|</div>
                    <div class="flow-node-highlight text-center">BatchSyncJob → 1 API call (stream) → iterate → update DB</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Fluxo Detalhado -->
    <section id="fluxo-detalhado" class="mb-12">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Fluxo detalhado do BatchSyncSearchTermStatsJob</h2>

        <!-- Step 1 -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">1. Verificacao de cota</h3>
            <p class="text-gray-600 dark:text-gray-300 mb-3">Antes de qualquer chamada, o job consulta o <code class="text-sm font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">GoogleAdsQuotaService</code>:</p>
            <div class="relative">
                <pre class="code-block"><code>if (!$quotaService->canMakeRequest()) {
    $this->release(60); // volta pra fila, tenta de novo em 60s
    return;
}</code></pre>
                <button onclick="copyCode(this)" class="copy-btn">Copiar</button>
            </div>
        </div>

        <!-- Step 2 -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">2. Resolucao do Customer ID</h3>
            <p class="text-gray-600 dark:text-gray-300 mb-3">O job resolve o <code class="text-sm font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">clientCustomerId</code> na mesma ordem de prioridade que os outros jobs:</p>
            <ol class="list-decimal list-inside text-gray-600 dark:text-gray-300 space-y-1">
                <li><code class="text-sm font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">config('app.client_customer_id')</code> (env var)</li>
                <li>Fallback: leitura do <code class="text-sm font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">google_ads_php.ini</code></li>
            </ol>
        </div>

        <!-- Step 3 -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">3. Chamada streaming</h3>
            <p class="text-gray-600 dark:text-gray-300 mb-3">
                Usa <code class="text-sm font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">searchStream</code> em vez de <code class="text-sm font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">search</code> (paginado).
                A resposta chega como stream gRPC — o PHP processa linha a linha sem carregar tudo em memoria:
            </p>
            <div class="relative mb-4">
                <pre class="code-block"><code>$stream = $googleAdsServiceClient->searchStream($request);

foreach ($stream->iterateAllElements() as $googleAdsRow) {
    // processa 1 termo por vez
}</code></pre>
                <button onclick="copyCode(this)" class="copy-btn">Copiar</button>
            </div>
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-yellow-400 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p class="text-sm text-yellow-800 dark:text-yellow-300">
                        Isso e importante para o VPS de 1GB em producao: mesmo com 11.000 termos, o consumo de memoria permanece baixo.
                    </p>
                </div>
            </div>
        </div>

        <!-- Step 4 -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">4. Lookup e update por chave unica</h3>
            <p class="text-gray-600 dark:text-gray-300 mb-3">Para cada linha retornada pela API, o job busca o <code class="text-sm font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">SearchTerm</code> existente no banco por sua chave unica natural:</p>
            <div class="relative mb-4">
                <pre class="code-block"><code>$existingTerm = SearchTerm::where('campaign_id', $campaignId)
    ->where('ad_group_id', $adGroupId)
    ->where('search_term', $searchTermText)
    ->first();</code></pre>
                <button onclick="copyCode(this)" class="copy-btn">Copiar</button>
            </div>

            <p class="text-gray-600 dark:text-gray-300 mb-3">Se encontra, atualiza:</p>
            <div class="relative mb-4">
                <pre class="code-block"><code>$existingTerm->update([
    'impressions'          => $impressions,
    'clicks'               => $clicks,
    'cost_micros'          => $costMicros,
    'ctr'                  => $ctr,
    'status'               => $status,
    'campaign_name'        => $campaign->getName(),
    'ad_group_name'        => $adGroup->getName(),
    'statistics_synced_at' => now(),
]);</code></pre>
                <button onclick="copyCode(this)" class="copy-btn">Copiar</button>
            </div>

            <p class="text-gray-600 dark:text-gray-300">
                Se <strong>nao</strong> encontra (termo existe no Google Ads mas nao no nosso banco), apenas incrementa o contador <code class="text-sm font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">notFoundCount</code>.
                Novos termos <strong>nao</strong> sao criados aqui — isso e responsabilidade do <code class="text-sm font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">SyncSearchTermsForDateJob</code>.
            </p>
        </div>

        <!-- Step 5 -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">5. Log de progresso</h3>
            <p class="text-gray-600 dark:text-gray-300 mb-3">A cada 500 termos processados, grava um log:</p>
            <div class="relative">
                <pre class="code-block"><code>BatchSyncSearchTermStatsJob: Progresso... {"processed":500,"updated":487,"not_found":13}</code></pre>
                <button onclick="copyCode(this)" class="copy-btn">Copiar</button>
            </div>
        </div>

        <!-- Step 6 -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">6. Resultado final</h3>
            <p class="text-gray-600 dark:text-gray-300 mb-3">Ao terminar o stream:</p>
            <div class="relative">
                <pre class="code-block"><code>BatchSyncSearchTermStatsJob: Sincronizacao em lote concluida. {"updated":10832,"not_found_in_db":215,"errors":0}</code></pre>
                <button onclick="copyCode(this)" class="copy-btn">Copiar</button>
            </div>
        </div>
    </section>

    <!-- Tratamento de Erros -->
    <section id="tratamento-erros" class="mb-12">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Tratamento de erros e retries</h2>

        <div class="overflow-x-auto mb-6">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cenario</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Comportamento</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">Cota excedida (pre-check)</td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300"><code class="font-mono bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded text-xs">release(60)</code> — volta pra fila em 60s</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300"><code class="font-mono bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded text-xs">ApiException</code> do Google Ads</td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300"><code class="font-mono bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded text-xs">fail()</code> — marca como falha, retry automatico</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">Excecao generica</td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300"><code class="font-mono bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded text-xs">fail()</code> — marca como falha, retry automatico</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">Erro em 1 termo individual</td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">Log do erro, continua com o proximo (nao falha o job inteiro)</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-3">Configuracao de retries:</p>
        <div class="relative">
            <pre class="code-block"><code>public $tries = 3;
public $backoff = [60, 300, 600]; // 1min, 5min, 10min</code></pre>
            <button onclick="copyCode(this)" class="copy-btn">Copiar</button>
        </div>
    </section>

    <!-- Relacao com Outros Jobs -->
    <section id="relacao-jobs" class="mb-12">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Relacao com outros jobs</h2>

        <div class="space-y-4 mb-6">
            <div class="border border-indigo-200 dark:border-indigo-800 rounded-lg p-4 bg-indigo-50/50 dark:bg-indigo-900/10">
                <div class="flex items-center mb-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200 mr-2">NOVO</span>
                    <code class="text-sm font-mono font-medium text-gray-900 dark:text-white">BatchSyncSearchTermStatsJob</code>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Atualiza stats em massa (1 API call)</p>
            </div>

            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex items-center mb-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200 mr-2">MANTIDO</span>
                    <code class="text-sm font-mono font-medium text-gray-900 dark:text-white">SyncSearchTermStatsJob</code>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Atualiza 1 termo (usado pelo AJAX de refresh individual)</p>
            </div>

            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex items-center mb-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200 mr-2">EXISTENTE</span>
                    <code class="text-sm font-mono font-medium text-gray-900 dark:text-white">SyncSearchTermsForDateJob</code>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Sincroniza termos por data (cria novos termos)</p>
            </div>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-2">O <code class="text-sm font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">SyncSearchTermStatsJob</code> continua existindo e sendo usado para:</p>
        <ul class="list-disc list-inside text-gray-600 dark:text-gray-300 space-y-1">
            <li>Refresh individual de stats via UI (botao "atualizar" em um termo)</li>
            <li><code class="text-sm font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">SearchTermObserver</code> ao criar um novo termo</li>
            <li>Apos adicionar keyword/negativar termo (<code class="text-sm font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">AddKeywordToAdGroupJob</code>, <code class="text-sm font-mono bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">AddNegativeKeywordJob</code>)</li>
        </ul>
    </section>

    <!-- Uso -->
    <section id="uso" class="mb-12">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Uso</h2>

        <div class="space-y-4">
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 dark:text-white mb-2">Ver o que seria feito (dry run)</h4>
                <div class="relative">
                    <pre class="code-block"><code>php artisan keywordai:sync-all-active-stats --dry-run</code></pre>
                    <button onclick="copyCode(this)" class="copy-btn">Copiar</button>
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 dark:text-white mb-2">Executar na fila default</h4>
                <div class="relative">
                    <pre class="code-block"><code>php artisan keywordai:sync-all-active-stats</code></pre>
                    <button onclick="copyCode(this)" class="copy-btn">Copiar</button>
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 dark:text-white mb-2">Executar numa fila especifica</h4>
                <div class="relative">
                    <pre class="code-block"><code>php artisan keywordai:sync-all-active-stats --queue=bulk</code></pre>
                    <button onclick="copyCode(this)" class="copy-btn">Copiar</button>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    // Active nav link on scroll (for in-page sections)
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('[href^="#"]');

    window.addEventListener('scroll', () => {
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            if (pageYOffset >= sectionTop - 100) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current) {
                link.classList.add('active');
            }
        });
    });
</script>
@endpush
