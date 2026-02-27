@extends('layouts.app')

@section('title', 'Log de Análise IA #' . $log->id)

@section('content')
<div class="py-6">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Log de Análise IA #{{ $log->id }}</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $log->created_at->format('d/m/Y H:i:s') }}</p>
            </div>
            <div>
                <a href="{{ route('ai-analysis-logs.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                    Voltar para a Lista
                </a>
            </div>
        </div>

        <!-- Status banner -->
        @if(!$log->success)
            <div class="mb-6 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" /></svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Análise falhou</h3>
                        @if($log->error_message)
                            <p class="mt-1 text-sm text-red-700 dark:text-red-300">{{ $log->error_message }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Metadata grid -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Informações da Chamada</h3>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700">
                <dl>
                    <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-4 sm:grid sm:grid-cols-4 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                        <dd class="mt-1 text-sm sm:mt-0">
                            @if($log->success)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Sucesso</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Falha</span>
                            @endif
                        </dd>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Reply Code</dt>
                        <dd class="mt-1 text-sm sm:mt-0">
                            @php
                                $code = $log->reply_code;
                                $codeClass = match(true) {
                                    $code === 200 => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                    $code === 0 => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                                    $code === 900 => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                    $code >= 400 => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                    default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                };
                                $codeLabel = match(true) {
                                    $code === 200 => '200 OK',
                                    $code === 0 => '0 (sem chamada)',
                                    $code === 900 => '900 (erro JSON)',
                                    default => (string) $code,
                                };
                            @endphp
                            <span class="px-2 inline-flex text-xs leading-5 font-mono font-semibold rounded-full {{ $codeClass }}">{{ $codeLabel }}</span>
                        </dd>
                    </div>
                    <div class="bg-white dark:bg-gray-800 px-4 py-4 sm:grid sm:grid-cols-4 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fonte</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:mt-0">{{ strtoupper($log->source) }}</dd>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Usuário</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:mt-0">{{ $log->user?->name ?? 'N/A (CLI)' }}</dd>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-4 sm:grid sm:grid-cols-4 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Modelo</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:mt-0">{{ ucfirst($log->model) }}</dd>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nome do Modelo</dt>
                        <dd class="mt-1 text-sm font-mono text-gray-900 dark:text-gray-100 sm:mt-0">{{ $log->model_name }}</dd>
                    </div>
                    <div class="bg-white dark:bg-gray-800 px-4 py-4 sm:grid sm:grid-cols-4 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipo de Análise</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:mt-0">{{ $log->analysis_type === 'date' ? 'Por Data' : 'Top Custo' }}</dd>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Filtro de Data</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:mt-0">{{ $log->date_filter ? $log->date_filter->format('d/m/Y') : 'N/A' }}</dd>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-4 sm:grid sm:grid-cols-4 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Limite de Termos</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:mt-0">{{ $log->term_limit }}</dd>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Termos Encontrados</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:mt-0">{{ $log->terms_found }}</dd>
                    </div>
                    <div class="bg-white dark:bg-gray-800 px-4 py-4 sm:grid sm:grid-cols-4 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Duração</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:mt-0">{{ $log->duration !== null ? number_format($log->duration, 2) . ' segundos' : 'N/A' }}</dd>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tamanho do Prompt</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:mt-0">{{ number_format($log->prompt_size) }} bytes</dd>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/50 px-4 py-4 sm:grid sm:grid-cols-6 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Prompt Tokens</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:mt-0">{{ $log->prompt_tokens !== null ? number_format($log->prompt_tokens) : 'N/A' }}</dd>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Completion Tokens</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:mt-0">{{ $log->completion_tokens !== null ? number_format($log->completion_tokens) : 'N/A' }}</dd>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Tokens</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:mt-0">{{ $log->total_tokens !== null ? number_format($log->total_tokens) : 'N/A' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Filtros da Análise -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Filtros Aplicados</h3>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700 p-4">
                <div class="bg-gray-100 dark:bg-gray-900 p-4 rounded overflow-x-auto">
                    <pre class="text-xs text-gray-800 dark:text-gray-200">{{ json_encode($log->filters, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        </div>

        <!-- Settings Snapshot -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Settings Snapshot</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configurações do modelo no momento da chamada.</p>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700 p-4">
                <div class="bg-gray-100 dark:bg-gray-900 p-4 rounded overflow-x-auto">
                    <pre class="text-xs text-gray-800 dark:text-gray-200">{{ json_encode($log->settings_snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        </div>

        <!-- Prompt -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Prompt</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ number_format($log->prompt_size) }} bytes</p>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700 p-4">
                <div class="bg-gray-100 dark:bg-gray-900 p-4 rounded overflow-x-auto max-h-[500px] overflow-y-auto">
                    <pre class="text-xs text-gray-800 dark:text-gray-200 whitespace-pre-wrap break-words">{{ $log->prompt }}</pre>
                </div>
            </div>
        </div>

        <!-- Reply -->
        @if($log->reply)
            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Resposta da IA</h3>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 p-4">
                    <div class="bg-gray-100 dark:bg-gray-900 p-4 rounded overflow-x-auto max-h-[500px] overflow-y-auto">
                        <pre class="text-xs text-gray-800 dark:text-gray-200 whitespace-pre-wrap break-words">{{ $log->reply }}</pre>
                    </div>
                </div>
            </div>
        @endif

        <!-- Error (if present) -->
        @if($log->error_message)
            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:px-6 bg-red-50 dark:bg-red-900/20">
                    <h3 class="text-lg leading-6 font-medium text-red-800 dark:text-red-200">Erro</h3>
                </div>
                <div class="border-t border-red-200 dark:border-red-800 p-4 bg-red-50 dark:bg-red-900/10">
                    <div class="bg-red-100 dark:bg-red-900/30 p-4 rounded overflow-x-auto">
                        <pre class="text-xs text-red-800 dark:text-red-200 whitespace-pre-wrap break-words">{{ $log->error_message }}</pre>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
