<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KeywordAI - Análise de Termos com IA</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
    @include('components.main-navigation')
    <div class="px-4 py-8">
        <h1 class="text-3xl font-bold mb-6 text-gray-800 dark:text-gray-200">Análise de Termos de Pesquisa com IA</h1>

        <!-- Estilos para destaque de linhas -->
        <style>
            .highlight-recommended {
                background-color: rgba(239, 68, 68, 0.2); /* Vermelho claro com transparência */
            }
        </style>

        <!-- Formulário de Análise -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
            <form id="analysis-form" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Tipo de Análise -->
                <div class="md:col-span-2 lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo de Análise</label>
                    <div class="flex space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="analysis_type" value="date" class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out" checked>
                            <span class="ml-2 text-gray-700 dark:text-gray-300">Por Data Específica</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="analysis_type" value="top" class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                            <span class="ml-2 text-gray-700 dark:text-gray-300">Top Termos por Custo</span>
                        </label>
                    </div>
                </div>

                <!-- Data (visível apenas quando o tipo de análise é "date") -->
                <div id="date-field" class="col-span-1">
                    <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data</label>
                    <input type="date" name="date" id="date" value="{{ date('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <!-- Filtro de Impressões -->
                <div>
                    <label for="min_impressions" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Impressões > que</label>
                    <input type="number" name="min_impressions" id="min_impressions" value="0" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <!-- Filtro de Cliques -->
                <div>
                    <label for="min_clicks" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cliques > que</label>
                    <input type="number" name="min_clicks" id="min_clicks" value="0" min="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <!-- Filtro de Custo -->
                <div>
                    <label for="min_cost" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Custo > que (R$)</label>
                    <input type="number" name="min_cost" id="min_cost" value="0" min="0" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <!-- Modelo de IA -->
                <div>
                    <label for="model" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Modelo de IA</label>
                    <select name="model" id="model" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @foreach($aiModels as $key => $label)
                            <option value="{{ $key }}" {{ $key === $defaultModel ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Limite de Termos -->
                <div>
                    <label for="limit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Limite de Termos</label>
                    <input type="number" name="limit" id="limit" value="50" min="1" max="100" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <div class="md:col-span-2 lg:col-span-3 flex items-end space-x-2">
                    <button type="submit" id="analyze-button" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <span id="analyze-text">Analisar com IA</span>
                        <span id="analyze-loading" class="hidden ml-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Área de Resultados (inicialmente oculta) -->
        <div id="results-container" class="hidden">
            <!-- Métricas da API -->
            <div id="api-metrics" class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Métricas da API</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Modelo Utilizado:</span>
                        <span id="model-used" class="text-gray-900 dark:text-gray-100"></span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Tempo de Resposta:</span>
                        <span id="response-time" class="text-gray-900 dark:text-gray-100"></span>
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Termos Analisados:</span>
                        <span id="terms-count" class="text-gray-900 dark:text-gray-100"></span>
                    </div>
                </div>
            </div>

            <!-- Ações em Lote -->
            <div id="batch-actions" class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Ações em Lote</h2>
                <div class="flex flex-col md:flex-row md:items-end space-y-4 md:space-y-0 md:space-x-4">
                    <div class="w-full md:w-1/3">
                        <label for="match_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo de Correspondência:</label>
                        <select id="match_type" name="match_type" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="exact" {{ $defaultMatchType === 'exact' ? 'selected' : '' }}>Exata (Exact)</option>
                            <option value="phrase" {{ $defaultMatchType === 'phrase' ? 'selected' : '' }}>Frase (Phrase)</option>
                            <option value="broad" {{ $defaultMatchType === 'broad' ? 'selected' : '' }}>Ampla (Broad)</option>
                        </select>
                    </div>
                    <div class="flex space-x-2">
                        <button id="select-all-button" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:border-gray-800 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Selecionar Todos
                        </button>
                        <button id="select-recommended-button" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:border-red-800 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Selecionar Recomendados
                        </button>
                        <button id="negate-selected-button" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-800 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150" disabled>
                            <span id="negate-text">Negativar Selecionados</span>
                            <span id="negate-loading" class="hidden ml-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabela de Resultados -->
            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
                <div class="overflow-x-auto">
                    <table id="results-table" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="w-16 px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    <input type="checkbox" id="select-all-checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Termo</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Campanha</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Grupo Anúncios</th>
                                <th scope="col" class="w-16 px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Impr.</th>
                                <th scope="col" class="w-16 px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cliques</th>
                                <th scope="col" class="w-20 px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Custo</th>
                                <th scope="col" class="w-16 px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">CTR</th>
                                <th scope="col" class="w-24 px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Negativar?</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Racional</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <!-- Os resultados serão inseridos aqui dinamicamente -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Área de Carregamento -->
        <div id="loading-container" class="hidden">
            <div class="flex flex-col items-center justify-center py-12">
                <svg class="animate-spin h-12 w-12 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="mt-4 text-lg text-gray-700 dark:text-gray-300">Analisando termos de pesquisa com IA...</p>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Isso pode levar alguns segundos.</p>
            </div>
        </div>

        <!-- Área de Erro -->
        <div id="error-container" class="hidden">
            <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Erro na Análise</h3>
                        <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                            <p id="error-message">Ocorreu um erro ao analisar os termos de pesquisa.</p>
                        </div>
                    </div>
                </div>
            </div>
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
            // Elementos do formulário
            const analysisForm = document.getElementById('analysis-form');
            const analysisTypeRadios = document.querySelectorAll('input[name="analysis_type"]');
            const dateField = document.getElementById('date-field');
            const analyzeButton = document.getElementById('analyze-button');
            const analyzeText = document.getElementById('analyze-text');
            const analyzeLoading = document.getElementById('analyze-loading');
            
            // Elementos de resultados
            const resultsContainer = document.getElementById('results-container');
            const loadingContainer = document.getElementById('loading-container');
            const errorContainer = document.getElementById('error-container');
            const errorMessage = document.getElementById('error-message');
            const resultsTable = document.getElementById('results-table');
            const resultsTableBody = resultsTable.querySelector('tbody');
            
            // Elementos de métricas
            const modelUsed = document.getElementById('model-used');
            const responseTime = document.getElementById('response-time');
            const termsCount = document.getElementById('terms-count');
            
            // Elementos de ações em lote
            const selectAllButton = document.getElementById('select-all-button');
            const selectRecommendedButton = document.getElementById('select-recommended-button');
            const negateSelectedButton = document.getElementById('negate-selected-button');
            const negateText = document.getElementById('negate-text');
            const negateLoading = document.getElementById('negate-loading');
            const selectAllCheckbox = document.getElementById('select-all-checkbox');
            const matchTypeSelect = document.getElementById('match_type');
            
            // Mostrar/ocultar o campo de data com base no tipo de análise
            analysisTypeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'date') {
                        dateField.classList.remove('hidden');
                    } else {
                        dateField.classList.add('hidden');
                    }
                });
            });
            
            // Manipular o envio do formulário
            analysisForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Desabilitar o botão e mostrar o indicador de carregamento
                analyzeButton.disabled = true;
                analyzeText.classList.add('hidden');
                analyzeLoading.classList.remove('hidden');
                
                // Esconder os resultados anteriores e mostrar o carregamento
                resultsContainer.classList.add('hidden');
                errorContainer.classList.add('hidden');
                loadingContainer.classList.remove('hidden');
                
                // Obter os dados do formulário
                const formData = new FormData(analysisForm);
                
                // Obter o token CSRF
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                try {
                    // Enviar a requisição AJAX
                    const response = await fetch('{{ route("ai-analysis.analyze") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });
                    
                    // Processar a resposta
                    const data = await response.json();
                    
                    if (response.ok && data.success) {
                        // Preencher as métricas
                        modelUsed.textContent = `${data.metrics.model} (${data.metrics.model_name})`;
                        responseTime.textContent = `${data.metrics.duration} segundos`;
                        termsCount.textContent = `${data.data.length} termos`;
                        
                        // Limpar a tabela de resultados
                        resultsTableBody.innerHTML = '';
                        
                        // Preencher a tabela de resultados
                        data.data.forEach(term => {
                            const row = document.createElement('tr');
                            
                            // Adicionar a classe de destaque se for recomendado para negativação
                            if (term.should_negate) {
                                row.classList.add('highlight-recommended');
                            }
                            
                            row.innerHTML = `
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                    <input type="checkbox" class="term-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                           data-term-id="${term.id}" 
                                           data-rationale="${term.rationale.replace(/"/g, '&quot;')}"
                                           ${term.should_negate ? 'checked' : ''}>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">${term.search_term}</td>
                                <td class="px-4 py-3 whitespace-normal text-sm text-gray-500 dark:text-gray-400">${term.campaign_name}</td>
                                <td class="px-4 py-3 whitespace-normal text-sm text-gray-500 dark:text-gray-400">${term.ad_group_name}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">${term.impressions}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">${term.clicks}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">${term.cost_formatted}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">${term.ctr}%</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-center font-semibold ${term.should_negate ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400'}">${term.should_negate ? 'SIM' : 'NÃO'}</td>
                                <td class="px-4 py-3 whitespace-normal text-sm text-gray-500 dark:text-gray-400">${term.rationale}</td>
                            `;
                            
                            resultsTableBody.appendChild(row);
                        });
                        
                        // Mostrar os resultados
                        loadingContainer.classList.add('hidden');
                        resultsContainer.classList.remove('hidden');
                        
                        // Atualizar o estado do botão de negativação
                        updateNegateButtonState();
                    } else {
                        // Mostrar a mensagem de erro
                        errorMessage.textContent = data.message || 'Ocorreu um erro ao analisar os termos de pesquisa.';
                        loadingContainer.classList.add('hidden');
                        errorContainer.classList.remove('hidden');
                    }
                } catch (error) {
                    console.error('Erro ao analisar termos:', error);
                    
                    // Mostrar a mensagem de erro
                    errorMessage.textContent = 'Ocorreu um erro ao analisar os termos de pesquisa. Por favor, tente novamente.';
                    loadingContainer.classList.add('hidden');
                    errorContainer.classList.remove('hidden');
                } finally {
                    // Reabilitar o botão e esconder o indicador de carregamento
                    analyzeButton.disabled = false;
                    analyzeText.classList.remove('hidden');
                    analyzeLoading.classList.add('hidden');
                }
            });
            
            // Manipular o clique no botão "Selecionar Todos"
            selectAllButton.addEventListener('click', function() {
                const checkboxes = document.querySelectorAll('.term-checkbox');
                const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
                
                checkboxes.forEach(checkbox => {
                    checkbox.checked = !allChecked;
                });
                
                selectAllCheckbox.checked = !allChecked;
                updateNegateButtonState();
            });
            
            // Manipular o clique no checkbox "Selecionar Todos" no cabeçalho da tabela
            selectAllCheckbox.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.term-checkbox');
                
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                
                updateNegateButtonState();
            });
            
            // Manipular o clique no botão "Selecionar Recomendados"
            selectRecommendedButton.addEventListener('click', function() {
                const rows = document.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const isRecommended = row.classList.contains('highlight-recommended');
                    const checkbox = row.querySelector('.term-checkbox');
                    
                    if (checkbox) {
                        checkbox.checked = isRecommended;
                    }
                });
                
                updateNegateButtonState();
            });
            
            // Manipular o clique nos checkboxes individuais
            resultsTableBody.addEventListener('change', function(e) {
                if (e.target.classList.contains('term-checkbox')) {
                    updateNegateButtonState();
                }
            });
            
            // Manipular o clique no botão "Negativar Selecionados"
            negateSelectedButton.addEventListener('click', async function() {
                // Obter os termos selecionados
                const selectedCheckboxes = document.querySelectorAll('.term-checkbox:checked');
                
                if (selectedCheckboxes.length === 0) {
                    return;
                }
                
                // Desabilitar o botão e mostrar o indicador de carregamento
                negateSelectedButton.disabled = true;
                negateText.classList.add('hidden');
                negateLoading.classList.remove('hidden');
                
                // Preparar os dados para enviar
                const terms = [];
                selectedCheckboxes.forEach(checkbox => {
                    terms.push({
                        id: checkbox.getAttribute('data-term-id'),
                        rationale: checkbox.getAttribute('data-rationale')
                    });
                });
                
                // Obter o tipo de correspondência selecionado
                const matchType = matchTypeSelect.value;
                
                // Obter o token CSRF
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                try {
                    // Enviar a requisição AJAX
                    const response = await fetch('{{ route("ai-analysis.negate") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            terms: terms,
                            match_type: matchType
                        })
                    });
                    
                    // Processar a resposta
                    const data = await response.json();
                    
                    if (response.ok && data.success) {
                        // Exibir a notificação de sucesso
                        showNotification(data.message || 'Termos negativados com sucesso!');
                        
                        // Desmarcar os checkboxes
                        selectedCheckboxes.forEach(checkbox => {
                            checkbox.checked = false;
                        });
                        
                        // Desmarcar o checkbox "Selecionar Todos"
                        selectAllCheckbox.checked = false;
                        
                        // Atualizar o estado do botão de negativação
                        updateNegateButtonState();
                    } else {
                        // Exibir a notificação de erro
                        showNotification(data.message || 'Erro ao negativar os termos selecionados.', true);
                    }
                } catch (error) {
                    console.error('Erro ao negativar termos:', error);
                    
                    // Exibir a notificação de erro
                    showNotification('Erro ao negativar os termos selecionados. Por favor, tente novamente.', true);
                } finally {
                    // Reabilitar o botão e esconder o indicador de carregamento
                    negateSelectedButton.disabled = false;
                    negateText.classList.remove('hidden');
                    negateLoading.classList.add('hidden');
                }
            });
            
            // Função para atualizar o estado do botão de negativação
            function updateNegateButtonState() {
                const selectedCheckboxes = document.querySelectorAll('.term-checkbox:checked');
                negateSelectedButton.disabled = selectedCheckboxes.length === 0;
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
