@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Detalhes da Palavra-chave Negativa</h1>
                <p class="mt-1 text-sm text-gray-600">Informações detalhadas sobre a palavra-chave negativa.</p>
            </div>
            <div>
                <a href="{{ route('negative-keywords.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    Voltar para a Lista
                </a>
            </div>
        </div>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    {{ $negativeKeyword->keyword }}
                    @if($negativeKeyword->match_type == 'broad')
                        <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            Ampla (Broad)
                        </span>
                    @elseif($negativeKeyword->match_type == 'phrase')
                        <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            Frase (Phrase)
                        </span>
                    @elseif($negativeKeyword->match_type == 'exact')
                        <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            Exata (Exact)
                        </span>
                    @else
                        <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                            {{ $negativeKeyword->match_type }}
                        </span>
                    @endif
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    Adicionada em {{ $negativeKeyword->created_at->format('d/m/Y H:i:s') }}
                </p>
            </div>
            <div class="border-t border-gray-200">
                <dl>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            ID
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $negativeKeyword->id }}
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Palavra-chave
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $negativeKeyword->keyword }}
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Tipo de Correspondência
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            @if($negativeKeyword->match_type == 'broad')
                                Ampla (Broad)
                            @elseif($negativeKeyword->match_type == 'phrase')
                                Frase (Phrase)
                            @elseif($negativeKeyword->match_type == 'exact')
                                Exata (Exact)
                            @else
                                {{ $negativeKeyword->match_type }}
                            @endif
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Motivo
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $negativeKeyword->reason ?? 'Nenhum motivo fornecido' }}
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            ID da Lista
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $negativeKeyword->list_id }}
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Resource Name (Google Ads)
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <code class="text-xs bg-gray-100 p-1 rounded">{{ $negativeKeyword->resource_name ?? 'N/A' }}</code>
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Adicionado por
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $negativeKeyword->createdBy->name ?? 'N/A' }} ({{ $negativeKeyword->createdBy->email ?? 'N/A' }})
                        </dd>
                    </div>
                    @if($negativeKeyword->updatedBy)
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Atualizado por
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $negativeKeyword->updatedBy->name ?? 'N/A' }} ({{ $negativeKeyword->updatedBy->email ?? 'N/A' }})
                        </dd>
                    </div>
                    @endif
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Data de Criação
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $negativeKeyword->created_at->format('d/m/Y H:i:s') }}
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Data de Atualização
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $negativeKeyword->updated_at->format('d/m/Y H:i:s') }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Logs de Atividade Relacionados -->
        <div class="mt-6">
            <h2 class="text-lg font-medium text-gray-900">Logs de Atividade Relacionados</h2>
            <p class="mt-1 text-sm text-gray-600">Atividades relacionadas a esta palavra-chave negativa.</p>
            
            <div class="mt-4">
                <a href="{{ route('activity-logs.index', ['entity_type' => 'negative_keyword', 'entity_id' => $negativeKeyword->id]) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Ver Logs de Atividade
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
