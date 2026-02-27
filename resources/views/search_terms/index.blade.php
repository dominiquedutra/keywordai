@extends('layouts.app')

@section('title', 'Termos de Pesquisa')

@section('styles')
<style>
    .highlight-added {
        background-color: rgba(34, 197, 94, 0.2);
    }
    .highlight-negated {
        background-color: rgba(239, 68, 68, 0.2);
    }
</style>
@endsection

@section('content')
        <h1 class="text-3xl font-bold mb-6 text-gray-800 dark:text-gray-200">Listagem de Termos de Pesquisa</h1>

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

        <!-- Batch Action Bar -->
        <div id="batch-action-bar" class="hidden mb-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4 flex items-center justify-between">
            <span class="text-sm text-amber-800 dark:text-amber-200">
                <span id="selected-count" class="font-bold">0</span> termo(s) selecionado(s)
            </span>
            <button
                id="batch-negate-button"
                type="button"
                class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700 active:bg-amber-800 focus:outline-none focus:border-amber-800 focus:ring ring-amber-300 disabled:opacity-25 transition ease-in-out duration-150"
                disabled
            >
                <span id="batch-negate-text">Negativar Rápido</span>
                <span id="batch-negate-loading" class="hidden ml-2">
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </button>
        </div>

        <!-- Tabela de Resultados -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="w-10 px-2 py-3 text-center">
                                <input type="checkbox" id="select-all-checkbox" class="rounded border-gray-300 dark:border-gray-600 text-amber-600 focus:ring-amber-500">
                            </th>
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
                                <td class="px-2 py-3 whitespace-nowrap text-center">
                                    <input type="checkbox" class="term-checkbox rounded border-gray-300 dark:border-gray-600 text-amber-600 focus:ring-amber-500" data-term-id="{{ $term->id }}">
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-center space-x-1">
                                    @php
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
                                <td colspan="14" class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
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
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Seleciona todos os botões de refresh
            const refreshButtons = document.querySelectorAll('.refresh-stats-button');

            // Adiciona o event listener para cada botão
            refreshButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Refresh button clicked', this);

                    const termId = this.getAttribute('data-term-id');
                    const currentButton = this;

                    currentButton.disabled = true;
                    currentButton.classList.add('opacity-50');
                    currentButton.innerHTML = '<svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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
                            showNotification(data.message || 'Estatísticas atualizadas com sucesso!');
                            updateTableRow(termId, data.data);
                        } else {
                            showNotification(data.message || 'Erro ao atualizar estatísticas', true);
                        }

                        setTimeout(() => {
                            currentButton.disabled = false;
                            currentButton.classList.remove('opacity-50');
                            currentButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>';
                        }, 2000);
                    })
                    .catch(error => {
                        showNotification('Erro ao atualizar estatísticas: ' + error.message, true);

                        currentButton.disabled = false;
                        currentButton.classList.remove('opacity-50');
                        currentButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>';
                    });
                });
            });

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

            modalButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();

                    const actionType = this.getAttribute('data-action');
                    const term = this.getAttribute('data-term');
                    const matchType = this.getAttribute('data-match-type');
                    const campaignName = this.getAttribute('data-campaign-name');
                    const adGroupName = this.getAttribute('data-ad-group-name');
                    const adGroupId = this.getAttribute('data-ad-group-id');
                    const keywordText = this.getAttribute('data-keyword-text');
                    const listId = '{{ config('googleads.default_negative_list_id') ?? '123456789' }}';

                    if (actionType === 'negate') {
                        modalTitle.textContent = 'Adicionar Palavra-Chave Negativa';
                        modalDescription.textContent = 'Adicione este termo como uma palavra-chave negativa para evitar que seus anúncios sejam exibidos para esta pesquisa.';
                        submitText.textContent = 'Salvar Negativação';
                        matchTypeLabel.textContent = 'Tipo de Correspondência para Negativação:';
                        broadOption.classList.remove('hidden');
                        document.getElementById('reason-container').classList.remove('hidden');
                        document.getElementById('ad-group-select-container').classList.add('hidden');
                    } else {
                        document.getElementById('reason-container').classList.add('hidden');
                        modalTitle.textContent = 'Adicionar Palavra-Chave';
                        modalDescription.textContent = 'Adicione este termo como uma palavra-chave para direcionar seus anúncios para esta pesquisa.';
                        submitText.textContent = 'Adicionar Palavra-Chave';
                        matchTypeLabel.textContent = 'Tipo de Correspondência:';
                        document.getElementById('ad-group-select-container').classList.remove('hidden');

                        const adGroupSelect = document.getElementById('ad-group-select');
                        if (adGroupSelect) {
                            const options = Array.from(adGroupSelect.options);
                            const option = options.find(opt => opt.value === adGroupId);
                            if (option) {
                                adGroupSelect.value = adGroupId;
                            }
                        }
                    }

                    termInput.value = term;
                    matchTypeSelect.value = matchType;
                    actionTypeInput.value = actionType;
                    listIdInput.value = listId;
                    adGroupIdInput.value = adGroupId;
                    adGroupNameInput.value = adGroupName;

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

                    errorMessage.classList.add('hidden');
                    modal.classList.remove('hidden');
                });
            });

            cancelButton.addEventListener('click', function() {
                modal.classList.add('hidden');
            });

            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                }
            });

            keywordForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                submitButton.disabled = true;
                loadingIndicator.classList.remove('hidden');
                errorMessage.classList.add('hidden');

                try {
                    const actionType = actionTypeInput.value;
                    const endpoint = actionType === 'negate' ? '/negative-keyword/add' : '/keyword/add';

                    const formData = new FormData();

                    if (actionType === 'negate') {
                        formData.append('term', termInput.value);
                        formData.append('match_type', matchTypeSelect.value);
                        formData.append('list_id', listIdInput.value);
                        formData.append('reason', document.getElementById('reason-input').value);
                    } else {
                        const adGroupSelect = document.getElementById('ad-group-select');
                        const selectedAdGroupId = adGroupSelect ? adGroupSelect.value : adGroupIdInput.value;

                        let selectedAdGroupName = adGroupNameInput.value;
                        if (adGroupSelect && adGroupSelect.selectedIndex >= 0) {
                            selectedAdGroupName = adGroupSelect.options[adGroupSelect.selectedIndex].text;
                        }

                        formData.append('search_term', termInput.value);
                        formData.append('match_type', matchTypeSelect.value);
                        formData.append('ad_group_id', selectedAdGroupId);
                        formData.append('ad_group_name', selectedAdGroupName);
                    }

                    formData.append('_token', document.querySelector('input[name="_token"]').value);

                    const response = await fetch(endpoint, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        const message = actionType === 'negate'
                            ? 'Palavra-chave negativa adicionada com sucesso!'
                            : 'Palavra-chave adicionada com sucesso!';

                        showNotification(message);
                        modal.classList.add('hidden');

                        const modifiedTerm = termInput.value;
                        const rows = document.querySelectorAll('tbody tr');
                        let targetRow = null;

                        for (const row of rows) {
                            const termCell = row.cells[2];
                            if (termCell && termCell.textContent.trim() === modifiedTerm) {
                                targetRow = row;
                                break;
                            }
                        }

                        if (targetRow) {
                            const refreshButton = targetRow.querySelector('.refresh-stats-button');
                            if (refreshButton) {
                                const termId = refreshButton.getAttribute('data-term-id');

                                try {
                                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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
                                            updateTableRow(termId, refreshData.data);
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
                        const responseData = await response.json().catch(() => null);

                        errorText.textContent = responseData && responseData.message
                            ? responseData.message
                            : 'Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente.';

                        errorMessage.classList.remove('hidden');
                    }
                } catch (error) {
                    console.error('Erro ao submeter formulário:', error);
                    errorText.textContent = 'Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente.';
                    errorMessage.classList.remove('hidden');
                } finally {
                    submitButton.disabled = false;
                    loadingIndicator.classList.add('hidden');
                }
            });

            function updateTableRow(termId, data) {
                const button = document.querySelector(`button[data-term-id="${termId}"]`);
                if (!button) return;

                const row = button.closest('tr');
                if (!row) return;

                try {
                    row.cells[7].textContent = data.formatted.impressions;
                    row.cells[8].textContent = data.formatted.clicks;
                    row.cells[9].textContent = data.formatted.cost;
                    row.cells[10].textContent = data.formatted.ctr;
                    row.cells[11].textContent = data.status;
                } catch (error) {
                    console.error('Erro ao atualizar células:', error);
                }
            }

            // === Batch Negate (Negativar Rápido) ===
            const selectAllCheckbox = document.getElementById('select-all-checkbox');
            const batchActionBar = document.getElementById('batch-action-bar');
            const selectedCountEl = document.getElementById('selected-count');
            const batchNegateButton = document.getElementById('batch-negate-button');
            const batchNegateText = document.getElementById('batch-negate-text');
            const batchNegateLoading = document.getElementById('batch-negate-loading');

            function updateBatchBar() {
                const checked = document.querySelectorAll('.term-checkbox:checked');
                const count = checked.length;
                selectedCountEl.textContent = count;
                batchNegateButton.disabled = count === 0;
                if (count > 0) {
                    batchActionBar.classList.remove('hidden');
                } else {
                    batchActionBar.classList.add('hidden');
                }
                // Sync select-all checkbox state
                const allCheckboxes = document.querySelectorAll('.term-checkbox');
                selectAllCheckbox.checked = allCheckboxes.length > 0 && checked.length === allCheckboxes.length;
                selectAllCheckbox.indeterminate = checked.length > 0 && checked.length < allCheckboxes.length;
            }

            selectAllCheckbox.addEventListener('change', function() {
                document.querySelectorAll('.term-checkbox').forEach(cb => {
                    cb.checked = selectAllCheckbox.checked;
                });
                updateBatchBar();
            });

            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('term-checkbox')) {
                    updateBatchBar();
                }
            });

            batchNegateButton.addEventListener('click', async function() {
                const checked = document.querySelectorAll('.term-checkbox:checked');
                if (checked.length === 0) return;

                const terms = Array.from(checked).map(cb => ({
                    id: parseInt(cb.getAttribute('data-term-id')),
                    rationale: null
                }));

                batchNegateButton.disabled = true;
                batchNegateText.textContent = 'Processando...';
                batchNegateLoading.classList.remove('hidden');

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    const response = await fetch('/ai-analysis/negate', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            terms: terms,
                            match_type: '{{ $defaultMatchType }}'
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        showNotification(data.message || `${terms.length} termo(s) negativado(s) com sucesso!`);
                        // Uncheck all and hide bar
                        document.querySelectorAll('.term-checkbox:checked').forEach(cb => {
                            cb.checked = false;
                            // Highlight the negated row
                            const row = cb.closest('tr');
                            if (row) row.classList.add('highlight-negated');
                        });
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = false;
                        updateBatchBar();
                    } else {
                        showNotification(data.message || 'Erro ao negativar termos.', true);
                    }
                } catch (error) {
                    console.error('Erro ao negativar em lote:', error);
                    showNotification('Erro ao negativar termos: ' + error.message, true);
                } finally {
                    batchNegateButton.disabled = false;
                    batchNegateText.textContent = 'Negativar Rápido';
                    batchNegateLoading.classList.add('hidden');
                    updateBatchBar();
                }
            });

            function showNotification(message, isError = false) {
                const notification = document.getElementById('notification');
                const notificationMessage = document.getElementById('notification-message');

                notificationMessage.textContent = message;

                if (isError) {
                    notification.classList.remove('bg-gray-800');
                    notification.classList.add('bg-red-600');
                } else {
                    notification.classList.remove('bg-red-600');
                    notification.classList.add('bg-gray-800');
                }

                notification.classList.remove('opacity-0');
                notification.classList.add('opacity-100');

                setTimeout(() => {
                    notification.classList.remove('opacity-100');
                    notification.classList.add('opacity-0');
                }, 5000);
            }
        });
    </script>
@endsection
