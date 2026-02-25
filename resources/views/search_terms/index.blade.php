<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KeywordAI - Termos de Pesquisa</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
    @include('components.main-navigation')
    <div class="px-4 py-8">
        <h1 class="text-3xl font-bold mb-6 text-gray-800 dark:text-gray-200">Listagem de Termos de Pesquisa</h1>

        <!-- Estilos para destaque de linhas -->
        <style>
            .highlight-added {
                background-color: rgba(34, 197, 94, 0.2); /* Verde claro com transparência */
            }
            .highlight-negated {
                background-color: rgba(239, 68, 68, 0.2); /* Vermelho claro com transparência */
            }
        </style>

        <!-- Filtros -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
            <form method="GET" action="{{ route('search-terms.index') }}" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div>
                    <label for="term" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Termo de Pesquisa</label>
                    <input type="text" name="term" id="term" value="{{ $filters['term'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="campaign_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Campanha</label>
                    <select name="campaign_name" id="campaign_name" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Todas</option>
                        @foreach($campaignNames as $name)
                            <option value="{{ $name }}" {{ ($filters['campaign_name'] ?? '') == $name ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="ad_group_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Grupo de Anúncios</label>
                    <select name="ad_group_name" id="ad_group_name" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Todos</option>
                        @foreach($adGroupNames as $name)
                            <option value="{{ $name }}" {{ ($filters['ad_group_name'] ?? '') == $name ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Todos</option>
                        <option value="ADDED" {{ ($filters['status'] ?? '') == 'ADDED' ? 'selected' : '' }}>Added</option>
                        <option value="EXCLUDED" {{ ($filters['status'] ?? '') == 'EXCLUDED' ? 'selected' : '' }}>Excluded</option>
                        <option value="NONE" {{ ($filters['status'] ?? '') == 'NONE' ? 'selected' : '' }}>None</option>
                        <option value="Added/Excluded" {{ ($filters['status'] ?? '') == 'Added/Excluded' ? 'selected' : '' }}>Added/Excluded</option>
                        {{-- Optionally populate dynamically from $statuses if needed --}}
                    </select>
                </div>
                <!-- Match Type Filter -->
                <div>
                    <label for="match_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Match Type</label>
                    <select name="match_type" id="match_type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Todos</option>
                        @foreach($matchTypes as $type)
                            <option value="{{ $type }}" {{ ($filters['match_type'] ?? '') == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Keyword Text Filter -->
                <div>
                    <label for="keyword_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Keyword Text</label>
                    <input type="text" name="keyword_text" id="keyword_text" value="{{ $filters['keyword_text'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <!-- Filtro de Impressões -->
                <div>
                    <label for="min_impressions" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Impressões > que</label>
                    <input type="number" name="min_impressions" id="min_impressions" value="{{ $filters['min_impressions'] ?? '' }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <!-- Filtro de Cliques -->
                <div>
                    <label for="min_clicks" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cliques > que</label>
                    <input type="number" name="min_clicks" id="min_clicks" value="{{ $filters['min_clicks'] ?? '' }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <!-- Filtro de Custo -->
                <div>
                    <label for="min_cost" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Custo > que (R$)</label>
                    <input type="number" name="min_cost" id="min_cost" value="{{ $filters['min_cost'] ?? '' }}" min="0" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <!-- Seletor de itens por página -->
                <div>
                    <label for="per_page" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Itens por página</label>
                    <select name="per_page" id="per_page" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @foreach($allowedPerPageValues as $value)
                            <option value="{{ $value }}" {{ ($perPage == $value) ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end md:col-span-3 lg:col-span-5 space-x-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Filtrar
                    </button>
                    <button type="button" onclick="window.location.href='{{ route('search-terms.index') }}'" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 active:bg-gray-700 focus:outline-none focus:border-gray-700 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Limpar
                    </button>
                </div>
            </form>
        </div>

        <!-- Paginação (Movida para cima) -->
        <div class="mb-6">
            {{ $searchTerms->appends(request()->query())->links() }}
        </div>

        <!-- Tabela de Resultados -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="w-24 px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ações</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Termo</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Campanha</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Grupo Anúncios</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Keyword</th>
                            <th scope="col" class="w-20 px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Match Type</th>
                            <th scope="col" class="w-16 px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                @php
                                    $linkParams = array_merge(request()->except(['sort_by', 'sort_direction']), ['sort_by' => 'impressions']);
                                    $currentSortBy = $filters['sort_by'] ?? null;
                                    $currentSortDir = $filters['sort_direction'] ?? 'desc';
                                    $linkParams['sort_direction'] = ($currentSortBy == 'impressions' && $currentSortDir == 'desc') ? 'asc' : 'desc';
                                @endphp
                                <a href="{{ route('search-terms.index', $linkParams) }}" class="inline-flex items-center">
                                    Impr.
                                    @if ($currentSortBy == 'impressions')
                                        @if ($currentSortDir == 'desc')
                                            <svg class="ml-1 w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                        @else
                                            <svg class="ml-1 w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
                                        @endif
                                    @endif
                                </a>
                            </th>
                            <th scope="col" class="w-16 px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                @php
                                    $linkParams = array_merge(request()->except(['sort_by', 'sort_direction']), ['sort_by' => 'clicks']);
                                    $currentSortBy = $filters['sort_by'] ?? null;
                                    $currentSortDir = $filters['sort_direction'] ?? 'desc';
                                    $linkParams['sort_direction'] = ($currentSortBy == 'clicks' && $currentSortDir == 'desc') ? 'asc' : 'desc';
                                @endphp
                                <a href="{{ route('search-terms.index', $linkParams) }}" class="inline-flex items-center">
                                    Cliques
                                    @if ($currentSortBy == 'clicks')
                                        @if ($currentSortDir == 'desc')
                                            <svg class="ml-1 w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                        @else
                                            <svg class="ml-1 w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
                                        @endif
                                    @endif
                                </a>
                            </th>
                            <th scope="col" class="w-20 px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                @php
                                    $linkParams = array_merge(request()->except(['sort_by', 'sort_direction']), ['sort_by' => 'cost_micros']);
                                    $currentSortBy = $filters['sort_by'] ?? null;
                                    $currentSortDir = $filters['sort_direction'] ?? 'desc';
                                    $linkParams['sort_direction'] = ($currentSortBy == 'cost_micros' && $currentSortDir == 'desc') ? 'asc' : 'desc';
                                @endphp
                                <a href="{{ route('search-terms.index', $linkParams) }}" class="inline-flex items-center">
                                    Custo
                                    @if ($currentSortBy == 'cost_micros')
                                        @if ($currentSortDir == 'desc')
                                            <svg class="ml-1 w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                        @else
                                            <svg class="ml-1 w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
                                        @endif
                                    @endif
                                </a>
                            </th>
                            <th scope="col" class="w-16 px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">CTR</th>
                            <th scope="col" class="w-20 px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th scope="col" class="w-28 px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                @php
                                    $linkParams = array_merge(request()->except(['sort_by', 'sort_direction']), ['sort_by' => 'first_seen_at']);
                                    $currentSortBy = $filters['sort_by'] ?? null;
                                    $currentSortDir = $filters['sort_direction'] ?? 'desc';
                                    $linkParams['sort_direction'] = ($currentSortBy == 'first_seen_at' && $currentSortDir == 'desc') ? 'asc' : 'desc';
                                @endphp
                                <a href="{{ route('search-terms.index', $linkParams) }}" class="inline-flex items-center">
                                    Primeira vez em
                                    @if ($currentSortBy == 'first_seen_at')
                                        @if ($currentSortDir == 'desc')
                                            <svg class="ml-1 w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                        @else
                                            <svg class="ml-1 w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
                                        @endif
                                    @endif
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($searchTerms as $term)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-center space-x-1">
                                    @php
                                        // Buscar o ID do banco de dados do grupo de anúncios correspondente ao ID do Google Ads
                                        $dbAdGroup = \App\Models\AdGroup::where('google_ad_group_id', $term->ad_group_id)->first();
                                        $dbAdGroupId = $dbAdGroup ? $dbAdGroup->id : null;
                                    @endphp
                                    <button 
                                        type="button" 
                                        class="open-keyword-modal-button inline-flex items-center p-1.5 border border-transparent text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                        data-action="add"
                                        data-term="{{ $term->search_term }}"
                                        data-match-type="exact"
                                        data-campaign-name="{{ $term->campaign_name }}"
                                        data-ad-group-name="{{ $term->ad_group_name }}"
                                        data-ad-group-id="{{ $dbAdGroupId }}"
                                        data-google-ad-group-id="{{ $term->ad_group_id }}"
                                        data-keyword-text="{{ $term->keyword_text }}"
                                        title="Adicionar Palavra-Chave"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                    <button 
                                        type="button" 
                                        class="open-keyword-modal-button inline-flex items-center p-1.5 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                        data-action="negate"
                                        data-term="{{ $term->search_term }}"
                                        data-match-type="phrase"
                                        data-campaign-name="{{ $term->campaign_name }}"
                                        data-ad-group-name="{{ $term->ad_group_name }}"
                                        data-ad-group-id="{{ $dbAdGroupId }}"
                                        data-google-ad-group-id="{{ $term->ad_group_id }}"
                                        data-keyword-text="{{ $term->keyword_text }}"
                                        title="Adicionar Palavra-Chave Negativa"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                        </svg>
                                    </button>
                                    <button 
                                        type="button" 
                                        data-term-id="{{ $term->id }}" 
                                        class="refresh-stats-button inline-flex items-center p-1.5 border border-transparent text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                        title="Atualizar estatísticas"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </button>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $term->search_term }}</td>
                                <td class="px-4 py-3 whitespace-normal text-sm text-gray-500 dark:text-gray-400">{{ $term->campaign_name }}</td>
                                <td class="px-4 py-3 whitespace-normal text-sm text-gray-500 dark:text-gray-400">{{ $term->ad_group_name }}</td>
                                <td class="px-4 py-3 whitespace-normal text-sm text-gray-500 dark:text-gray-400">{{ $term->keyword_text ?? '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $term->match_type ?? '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">{{ number_format($term->impressions, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">{{ number_format($term->clicks, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">R$ {{ number_format($term->cost_micros / 1000000, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">{{ number_format($term->ctr, 0, ',', '.') }}%</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $term->status }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $term->first_seen_at ? \Carbon\Carbon::parse($term->first_seen_at)->format('d/m/Y') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Nenhum termo de pesquisa encontrado com os filtros aplicados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paginação (Duplicada no final para conveniência) -->
        <div class="mt-6">
            {{ $searchTerms->appends(request()->query())->links() }}
        </div>

    </div>

    <!-- Modal para Adicionar/Negativar Palavras-Chave -->
    <div id="keyword-modal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4">
            <!-- Cabeçalho do Modal -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 id="modal-title" class="text-lg font-medium text-gray-900 dark:text-gray-100"></h3>
                <p id="modal-description" class="mt-1 text-sm text-gray-500 dark:text-gray-400"></p>
            </div>
            
            <!-- Informações do Grupo de Anúncios -->
            <div id="ad-group-info" class="px-6 py-4 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-200 dark:border-blue-800 hidden">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    <strong class="font-semibold text-gray-700 dark:text-gray-300">Grupo de Anúncios:</strong> 
                    <span id="ad-group-name"></span> <span id="ad-group-id-display"></span>
                </p>
                <p id="campaign-info" class="text-sm text-gray-600 dark:text-gray-400 mt-1 hidden">
                    <strong class="font-semibold text-gray-700 dark:text-gray-300">Campanha:</strong> 
                    <span id="campaign-name"></span>
                </p>
                <p id="keyword-info" class="text-sm text-gray-600 dark:text-gray-400 mt-1 hidden">
                    <strong class="font-semibold text-gray-700 dark:text-gray-300">Palavra-Chave Original:</strong> 
                    <span id="keyword-text"></span>
                </p>
            </div>
            
            <!-- Mensagem de Erro -->
            <div id="error-message" class="px-6 py-4 bg-red-100 dark:bg-red-900/20 border-b border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 hidden">
                <strong class="font-bold">Erro!</strong>
                <span id="error-text" class="block"></span>
            </div>
            
            <!-- Formulário -->
            <form id="keyword-form" class="px-6 py-4">
                <!-- Campo de termo (editável) -->
                <div class="mb-4">
                    <label for="term-input" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Termo de Pesquisa (Editável):
                    </label>
                    <input 
                        type="text" 
                        id="term-input" 
                        name="term" 
                        required 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    >
                </div>
                
                <!-- Campo de seleção de Grupo de Anúncios (apenas para adicionar palavra-chave) -->
                <div id="ad-group-select-container" class="mb-4 hidden">
                    <label for="ad-group-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Grupo de Anúncios:
                    </label>
                    <select 
                        id="ad-group-select" 
                        name="ad_group_id" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    >
                        @if(isset($adGroups) && count($adGroups) > 0)
                            @foreach($adGroups as $group)
                                <option value="{{ $group['id'] }}">{{ $group['text'] }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                
                <!-- Campo de tipo de correspondência -->
                <div class="mb-4">
                    <label for="match-type-select" id="match-type-label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Tipo de Correspondência:
                    </label>
                    <select 
                        id="match-type-select" 
                        name="match_type" 
                        required 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    >
                        <option value="broad" id="broad-option">Ampla (Broad)</option>
                        <option value="phrase">Frase (Phrase)</option>
                        <option value="exact">Exata (Exact)</option>
                    </select>
                </div>
                
                <!-- Campo de motivo (apenas para negativação) -->
                <div id="reason-container" class="mb-4 hidden">
                    <label for="reason-input" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Motivo da Negativação:
                    </label>
                    <textarea
                        id="reason-input"
                        name="reason"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        placeholder="Informe o motivo para adicionar esta palavra-chave negativa"
                    ></textarea>
                </div>
                
                <!-- Campos ocultos -->
                <input type="hidden" id="action-type-input" name="action_type">
                <input type="hidden" id="list-id-input" name="list_id">
                <input type="hidden" id="ad-group-id-input" name="ad_group_id">
                <input type="hidden" id="ad-group-name-input" name="ad_group_name">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                
                <!-- Botões -->
                <div class="mt-6 flex justify-between">
                    <button 
                        type="button" 
                        id="cancel-button"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="submit" 
                        id="submit-button"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        <span id="submit-text">Salvar</span>
                        <span id="loading-indicator" class="hidden ml-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Notificação de status -->
    <div id="notification" class="fixed bottom-4 right-4 px-4 py-2 bg-gray-800 text-white rounded shadow-lg transform transition-opacity duration-300 opacity-0 pointer-events-none">
        <span id="notification-message"></span>
    </div>

    <!-- CSRF Token para requisições AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Seleciona todos os botões de refresh
            const refreshButtons = document.querySelectorAll('.refresh-stats-button');
            
            // Adiciona o event listener para cada botão
            refreshButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Refresh button clicked', this);
                    
                    // Obtém o ID do termo a partir do atributo data
                    const termId = this.getAttribute('data-term-id');
                    
                    // Referência ao botão atual para uso dentro das funções anônimas
                    const currentButton = this;
                    
                    // Desabilita o botão e muda o ícone para indicar carregamento
                    currentButton.disabled = true;
                    currentButton.classList.add('opacity-50');
                    currentButton.innerHTML = '<svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
                    
                    // Obtém o token CSRF do meta tag
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    
                    // Faz a requisição AJAX para a rota de refresh
                    fetch(`/search-terms/${termId}/refresh`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erro ao atualizar estatísticas');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Exibe a notificação de sucesso
                            showNotification(data.message || 'Estatísticas atualizadas com sucesso!');
                            
                            // Atualiza os dados na tabela
                            updateTableRow(termId, data.data);
                        } else {
                            // Exibe a notificação de erro
                            showNotification(data.message || 'Erro ao atualizar estatísticas', true);
                        }
                        
                        // Restaura o botão após 2 segundos
                        setTimeout(() => {
                            currentButton.disabled = false;
                            currentButton.classList.remove('opacity-50');
                            currentButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>';
                        }, 2000);
                    })
                    .catch(error => {
                        // Exibe a notificação de erro
                        showNotification('Erro ao atualizar estatísticas: ' + error.message, true);
                        
                        // Restaura o botão imediatamente em caso de erro
                        currentButton.disabled = false;
                        currentButton.classList.remove('opacity-50');
                        currentButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>';
                    });
                });
            });
            
            // Seleciona todos os botões de modal e o modal
            const modalButtons = document.querySelectorAll('.open-keyword-modal-button');
            const modal = document.getElementById('keyword-modal');
            const keywordForm = document.getElementById('keyword-form');
            const cancelButton = document.getElementById('cancel-button');
            const submitButton = document.getElementById('submit-button');
            const loadingIndicator = document.getElementById('loading-indicator');
            const submitText = document.getElementById('submit-text');
            const errorMessage = document.getElementById('error-message');
            const errorText = document.getElementById('error-text');
            const adGroupInfo = document.getElementById('ad-group-info');
            const campaignInfo = document.getElementById('campaign-info');
            const keywordInfo = document.getElementById('keyword-info');
            const modalTitle = document.getElementById('modal-title');
            const modalDescription = document.getElementById('modal-description');
            const termInput = document.getElementById('term-input');
            const matchTypeSelect = document.getElementById('match-type-select');
            const matchTypeLabel = document.getElementById('match-type-label');
            const broadOption = document.getElementById('broad-option');
            const actionTypeInput = document.getElementById('action-type-input');
            const listIdInput = document.getElementById('list-id-input');
            const adGroupIdInput = document.getElementById('ad-group-id-input');
            const adGroupNameInput = document.getElementById('ad-group-name-input');
            const adGroupNameDisplay = document.getElementById('ad-group-name');
            const adGroupIdDisplay = document.getElementById('ad-group-id-display');
            const campaignNameDisplay = document.getElementById('campaign-name');
            const keywordTextDisplay = document.getElementById('keyword-text');
            
            // Adiciona o event listener para cada botão
            modalButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Modal button clicked', this);
                    
                    // Obtém os dados do termo a partir dos atributos data
                    const actionType = this.getAttribute('data-action');
                    const term = this.getAttribute('data-term');
                    const matchType = this.getAttribute('data-match-type');
                    const campaignName = this.getAttribute('data-campaign-name');
                    const adGroupName = this.getAttribute('data-ad-group-name');
                    const adGroupId = this.getAttribute('data-ad-group-id');
                    const keywordText = this.getAttribute('data-keyword-text');
                    const listId = '{{ config('googleads.default_negative_list_id') ?? '123456789' }}'; // ID da lista padrão de negativação
                    
                    // Log detalhado dos atributos do botão
                    console.log('Botão clicado - Atributos:', {
                        element: this,
                        actionType,
                        term,
                        matchType,
                        campaignName,
                        adGroupName,
                        adGroupId,
                        keywordText,
                        listId,
                        allAttributes: Array.from(this.attributes).map(attr => `${attr.name}="${attr.value}"`)
                    });
                    
                    // Configura o modal com base no tipo de ação
                    if (actionType === 'negate') {
                        modalTitle.textContent = 'Adicionar Palavra-Chave Negativa';
                        modalDescription.textContent = 'Adicione este termo como uma palavra-chave negativa para evitar que seus anúncios sejam exibidos para esta pesquisa.';
                        submitText.textContent = 'Salvar Negativação';
                        matchTypeLabel.textContent = 'Tipo de Correspondência para Negativação:';
                        broadOption.classList.remove('hidden');
                        
                        // Mostra o campo de motivo para negativação
                        document.getElementById('reason-container').classList.remove('hidden');
                        
                        // Esconde o campo de seleção de grupo de anúncios para negativação
                        document.getElementById('ad-group-select-container').classList.add('hidden');
                    } else {
                        // Esconde o campo de motivo para adição de palavra-chave
                        document.getElementById('reason-container').classList.add('hidden');
                        modalTitle.textContent = 'Adicionar Palavra-Chave';
                        modalDescription.textContent = 'Adicione este termo como uma palavra-chave para direcionar seus anúncios para esta pesquisa.';
                        submitText.textContent = 'Adicionar Palavra-Chave';
                        matchTypeLabel.textContent = 'Tipo de Correspondência:';
                        // Removida a linha que escondia a opção "Ampla"
                        // broadOption.classList.add('hidden');
                        
                        // Mostra o campo de seleção de grupo de anúncios para adição de palavra-chave
                        document.getElementById('ad-group-select-container').classList.remove('hidden');
                        
                        // Seleciona o grupo de anúncios padrão (o que foi clicado)
                        const adGroupSelect = document.getElementById('ad-group-select');
                        if (adGroupSelect) {
                            // Procura a opção com o ID do grupo de anúncios
                            const options = Array.from(adGroupSelect.options);
                            const option = options.find(opt => opt.value === adGroupId);
                            if (option) {
                                adGroupSelect.value = adGroupId;
                            }
                        }
                    }
                    
                    // Preenche os campos do formulário
                    termInput.value = term;
                    matchTypeSelect.value = matchType;
                    actionTypeInput.value = actionType;
                    listIdInput.value = listId;
                    adGroupIdInput.value = adGroupId;
                    adGroupNameInput.value = adGroupName;
                    
                    // Exibe as informações do grupo de anúncios se disponíveis
                    if (adGroupName) {
                        adGroupInfo.classList.remove('hidden');
                        adGroupNameDisplay.textContent = adGroupName;
                        adGroupIdDisplay.textContent = adGroupId ? `(ID: ${adGroupId})` : '';
                        
                        if (campaignName) {
                            campaignInfo.classList.remove('hidden');
                            campaignNameDisplay.textContent = campaignName;
                        } else {
                            campaignInfo.classList.add('hidden');
                        }
                        
                        if (keywordText) {
                            keywordInfo.classList.remove('hidden');
                            keywordTextDisplay.textContent = keywordText;
                        } else {
                            keywordInfo.classList.add('hidden');
                        }
                    } else {
                        adGroupInfo.classList.add('hidden');
                    }
                    
                    // Limpa mensagens de erro
                    errorMessage.classList.add('hidden');
                    
                    // Exibe o modal
                    modal.classList.remove('hidden');
                });
            });
            
            // Fecha o modal quando o botão Cancelar é clicado
            cancelButton.addEventListener('click', function() {
                modal.classList.add('hidden');
            });
            
            // Fecha o modal quando o usuário clica fora dele
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                }
            });
            
            // Manipula o envio do formulário
            keywordForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Desabilita o botão de envio e mostra o indicador de carregamento
                submitButton.disabled = true;
                loadingIndicator.classList.remove('hidden');
                errorMessage.classList.add('hidden');
                
                try {
                    // Determina o endpoint com base no tipo de ação
                    const actionType = actionTypeInput.value;
                    const endpoint = actionType === 'negate' ? '/negative-keyword/add' : '/keyword/add';
                    
                    // Cria o objeto FormData
                    const formData = new FormData();
                    
                    if (actionType === 'negate') {
                        formData.append('term', termInput.value);
                        formData.append('match_type', matchTypeSelect.value);
                        formData.append('list_id', listIdInput.value);
                        formData.append('reason', document.getElementById('reason-input').value);
                    } else {
                        // Usar o valor selecionado no dropdown de grupo de anúncios
                        const adGroupSelect = document.getElementById('ad-group-select');
                        const selectedAdGroupId = adGroupSelect ? adGroupSelect.value : adGroupIdInput.value;
                        
                        // Obter o texto do grupo de anúncios selecionado
                        let selectedAdGroupName = adGroupNameInput.value;
                        if (adGroupSelect && adGroupSelect.selectedIndex >= 0) {
                            selectedAdGroupName = adGroupSelect.options[adGroupSelect.selectedIndex].text;
                        }
                        
                        formData.append('search_term', termInput.value);
                        formData.append('match_type', matchTypeSelect.value);
                        formData.append('ad_group_id', selectedAdGroupId);
                        formData.append('ad_group_name', selectedAdGroupName);
                        
                        console.log('Submitting with ad_group_id:', selectedAdGroupId, 'ad_group_name:', selectedAdGroupName);
                    }
                    
                    // Adiciona o token CSRF
                    formData.append('_token', document.querySelector('input[name="_token"]').value);
                    
                    // Envia a requisição
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    
                    // Processa a resposta
                    if (response.ok) {
                        // Exibe a notificação de sucesso
                        const message = actionType === 'negate' 
                            ? 'Palavra-chave negativa adicionada com sucesso!' 
                            : 'Palavra-chave adicionada com sucesso!';
                        
                        showNotification(message);
                        
                        // Fecha o modal
                        modal.classList.add('hidden');
                        
                        // Armazena o termo que foi modificado e o tipo de ação
                        const modifiedTerm = termInput.value;
                        
                        // Encontra a linha da tabela correspondente ao termo
                        const rows = document.querySelectorAll('tbody tr');
                        let targetRow = null;
                        
                        for (const row of rows) {
                            const termCell = row.cells[1]; // A célula que contém o termo (índice 1)
                            if (termCell && termCell.textContent.trim() === modifiedTerm) {
                                targetRow = row;
                                break;
                            }
                        }
                        
                        if (targetRow) {
                            // Executa o refresh de estatísticas para o termo
                            const refreshButton = targetRow.querySelector('.refresh-stats-button');
                            if (refreshButton) {
                                const termId = refreshButton.getAttribute('data-term-id');
                                
                                try {
                                    // Obtém o token CSRF
                                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                                    
                                    // Faz a requisição AJAX para atualizar as estatísticas
                                    const refreshResponse = await fetch(`/search-terms/${termId}/refresh`, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': csrfToken,
                                            'Accept': 'application/json',
                                            'Content-Type': 'application/json'
                                        }
                                    });
                                    
                                    if (refreshResponse.ok) {
                                        const refreshData = await refreshResponse.json();
                                        
                                        if (refreshData.success) {
                                            // Atualiza os dados na linha da tabela
                                            updateTableRow(termId, refreshData.data);
                                            
                                            // Aplica o destaque à linha (permanece até refresh da página)
                                            const highlightClass = actionType === 'negate' ? 'highlight-negated' : 'highlight-added';
                                            targetRow.classList.add(highlightClass);
                                        }
                                    }
                                } catch (refreshError) {
                                    console.error('Erro ao atualizar estatísticas:', refreshError);
                                }
                            }
                        }
                    } else {
                        // Tenta obter a mensagem de erro da resposta
                        const responseData = await response.json().catch(() => null);
                        
                        // Exibe a mensagem de erro
                        errorText.textContent = responseData && responseData.message 
                            ? responseData.message 
                            : 'Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente.';
                        
                        errorMessage.classList.remove('hidden');
                    }
                } catch (error) {
                    console.error('Erro ao submeter formulário:', error);
                    
                    // Exibe a mensagem de erro
                    errorText.textContent = 'Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente.';
                    errorMessage.classList.remove('hidden');
                } finally {
                    // Reabilita o botão de envio e esconde o indicador de carregamento
                    submitButton.disabled = false;
                    loadingIndicator.classList.add('hidden');
                }
            });
            
            // Função para atualizar os dados na linha da tabela
            function updateTableRow(termId, data) {
                // Encontra a linha da tabela correspondente ao termo
                const button = document.querySelector(`button[data-term-id="${termId}"]`);
                if (!button) {
                    console.error(`Botão com data-term-id="${termId}" não encontrado`);
                    return;
                }
                
                const row = button.closest('tr');
                if (!row) {
                    console.error(`Linha para o termo ID ${termId} não encontrada`);
                    return;
                }
                
                console.log(`Atualizando linha para termo ID ${termId}:`, {
                    totalCells: row.cells.length,
                    data: data
                });
                
                // Atualiza os valores nas células
                try {
                    // Impressões (7ª coluna, índice 6)
                    row.cells[6].textContent = data.formatted.impressions;
                    console.log(`Célula 6 (Impressões) atualizada para: ${data.formatted.impressions}`);
                    
                    // Cliques (8ª coluna, índice 7)
                    row.cells[7].textContent = data.formatted.clicks;
                    console.log(`Célula 7 (Cliques) atualizada para: ${data.formatted.clicks}`);
                    
                    // Custo (9ª coluna, índice 8)
                    row.cells[8].textContent = data.formatted.cost;
                    console.log(`Célula 8 (Custo) atualizada para: ${data.formatted.cost}`);
                    
                    // CTR (10ª coluna, índice 9)
                    row.cells[9].textContent = data.formatted.ctr;
                    console.log(`Célula 9 (CTR) atualizada para: ${data.formatted.ctr}`);
                    
                    // Status (11ª coluna, índice 10)
                    row.cells[10].textContent = data.status;
                    console.log(`Célula 10 (Status) atualizada para: ${data.status}`);
                    
                    console.log('Atualização da linha concluída com sucesso');
                } catch (error) {
                    console.error('Erro ao atualizar células:', error);
                    console.log('Estrutura da linha:', Array.from(row.cells).map((cell, index) => `Célula ${index}: ${cell.textContent}`));
                }
            }
            
            // Função para exibir notificações
            function showNotification(message, isError = false) {
                const notification = document.getElementById('notification');
                const notificationMessage = document.getElementById('notification-message');
                
                // Define a mensagem
                notificationMessage.textContent = message;
                
                // Aplica estilo baseado no tipo de notificação
                if (isError) {
                    notification.classList.remove('bg-gray-800');
                    notification.classList.add('bg-red-600');
                } else {
                    notification.classList.remove('bg-red-600');
                    notification.classList.add('bg-gray-800');
                }
                
                // Exibe a notificação
                notification.classList.remove('opacity-0');
                notification.classList.add('opacity-100');
                
                // Esconde a notificação após 5 segundos
                setTimeout(() => {
                    notification.classList.remove('opacity-100');
                    notification.classList.add('opacity-0');
                }, 5000);
            }
        });
    </script>
</body>
</html>
