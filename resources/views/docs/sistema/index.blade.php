@extends('docs.layout')

@section('title', 'Sistema')

@section('docs-content')
    <section class="mb-12">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Documentacao do Sistema</h1>
        <p class="text-lg text-gray-600 dark:text-gray-300 mb-8">
            Documentacao tecnica interna do KeywordAI — arquitetura, fluxos de dados e decisoes de design.
        </p>

        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Topicos</h2>

        <div class="grid md:grid-cols-2 gap-6">
            <!-- Batch Stats Sync -->
            <a href="{{ route('docs.sistema.batch-stats-sync') }}"
               class="group block border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:border-indigo-300 dark:hover:border-indigo-700 hover:shadow-md transition-all">
                <div class="flex items-start">
                    <div class="flex-shrink-0 h-10 w-10 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center">
                        <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                            Batch Stats Sync
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Como o sistema sincroniza estatisticas de 11.000+ termos com 1 unica chamada a API do Google Ads, em vez de 11.000 chamadas individuais.
                        </p>
                        <div class="mt-3 flex items-center text-sm text-indigo-600 dark:text-indigo-400">
                            <span>Ler documentacao</span>
                            <svg class="ml-1 h-4 w-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </section>

    <!-- Quick Reference -->
    <section class="mb-12">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Referencia Rapida</h2>

        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Comandos Artisan Principais</h3>
            <div class="space-y-3">
                <div class="flex items-start">
                    <code class="text-sm font-mono text-indigo-600 dark:text-indigo-400 whitespace-nowrap">keywordai:sync-all-active-stats</code>
                    <span class="ml-3 text-sm text-gray-600 dark:text-gray-400">Sincroniza estatisticas em lote (1 API call)</span>
                </div>
                <div class="flex items-start">
                    <code class="text-sm font-mono text-indigo-600 dark:text-indigo-400 whitespace-nowrap">keywordai:full-sync</code>
                    <span class="ml-3 text-sm text-gray-600 dark:text-gray-400">Sync historico dia-a-dia com retry/resume</span>
                </div>
                <div class="flex items-start">
                    <code class="text-sm font-mono text-indigo-600 dark:text-indigo-400 whitespace-nowrap">keywordai:sync-entities</code>
                    <span class="ml-3 text-sm text-gray-600 dark:text-gray-400">Sincroniza campanhas e ad groups</span>
                </div>
                <div class="flex items-start">
                    <code class="text-sm font-mono text-indigo-600 dark:text-indigo-400 whitespace-nowrap">keywordai:analyze-search-terms</code>
                    <span class="ml-3 text-sm text-gray-600 dark:text-gray-400">Analise IA por data</span>
                </div>
                <div class="flex items-start">
                    <code class="text-sm font-mono text-indigo-600 dark:text-indigo-400 whitespace-nowrap">keywordai:analyze-top-search-terms</code>
                    <span class="ml-3 text-sm text-gray-600 dark:text-gray-400">Analise IA dos termos de maior custo</span>
                </div>
            </div>
        </div>
    </section>
@endsection
