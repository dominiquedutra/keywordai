@extends('layouts.app')

@section('title', 'Log de Análise IA')

@section('content')
<div class="py-6">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Log de Análise IA</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Registro de todas as chamadas de análise de IA com detalhes completos.</p>
        </div>

        <!-- Filtros -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6 p-4">
            <form action="{{ route('ai-analysis-logs.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Modelo -->
                    <div>
                        <label for="model" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Modelo</label>
                        <select id="model" name="model" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">Todos</option>
                            <option value="gemini" {{ ($filters['model'] ?? '') === 'gemini' ? 'selected' : '' }}>Gemini</option>
                            <option value="openai" {{ ($filters['model'] ?? '') === 'openai' ? 'selected' : '' }}>OpenAI</option>
                            <option value="openrouter" {{ ($filters['model'] ?? '') === 'openrouter' ? 'selected' : '' }}>OpenRouter</option>
                        </select>
                    </div>

                    <!-- Fonte -->
                    <div>
                        <label for="source" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fonte</label>
                        <select id="source" name="source" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">Todas</option>
                            <option value="web" {{ ($filters['source'] ?? '') === 'web' ? 'selected' : '' }}>Web</option>
                            <option value="api" {{ ($filters['source'] ?? '') === 'api' ? 'selected' : '' }}>API</option>
                            <option value="cli" {{ ($filters['source'] ?? '') === 'cli' ? 'selected' : '' }}>CLI</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="success" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <select id="success" name="success" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">Todos</option>
                            <option value="1" {{ ($filters['success'] ?? '') === '1' ? 'selected' : '' }}>Sucesso</option>
                            <option value="0" {{ isset($filters['success']) && $filters['success'] === '0' ? 'selected' : '' }}>Falha</option>
                        </select>
                    </div>

                    <!-- Data Inicial -->
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data Inicial</label>
                        <input type="date" name="start_date" id="start_date" value="{{ $filters['start_date'] ?? '' }}" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <!-- Data Final -->
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data Final</label>
                        <input type="date" name="end_date" id="end_date" value="{{ $filters['end_date'] ?? '' }}" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div class="flex justify-end">
                    <a href="{{ route('ai-analysis-logs.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 mr-2">
                        Limpar Filtros
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Filtrar
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabela -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Data/Hora</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fonte</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Modelo</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipo</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Termos</th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Reply Code</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Duração</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tokens</th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($logs as $log)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-500 dark:text-gray-400">
                                    {{ $log->id }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    @if($log->source === 'web')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Web</span>
                                    @elseif($log->source === 'api')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">API</span>
                                    @elseif($log->source === 'cli')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">CLI</span>
                                    @else
                                        <span class="text-gray-500">{{ $log->source }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    <span class="font-medium">{{ ucfirst($log->model) }}</span>
                                    <br><span class="text-xs text-gray-500 dark:text-gray-400">{{ $log->model_name }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $log->analysis_type === 'date' ? 'Data' : 'Top custo' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">
                                    {{ $log->terms_found }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                    @php
                                        $code = $log->reply_code;
                                        $codeClass = match(true) {
                                            $code === 200 => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                            $code === 0 => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                                            $code === 900 => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                            $code >= 400 => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                        };
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-mono font-semibold rounded-full {{ $codeClass }}">
                                        {{ $code }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">
                                    {{ $log->duration !== null ? number_format($log->duration, 2) . 's' : '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">
                                    {{ $log->total_tokens ? number_format($log->total_tokens) : '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                    @if($log->success)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Sucesso</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Falha</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('ai-analysis-logs.show', $log) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Detalhes</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-4 py-8 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                    Nenhum log de análise de IA encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                {{ $logs->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
