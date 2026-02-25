@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Detalhes do Log de Atividade</h1>
                <p class="mt-1 text-sm text-gray-600">Informações detalhadas sobre a atividade registrada.</p>
            </div>
            <div>
                <a href="{{ route('activity-logs.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
                    @if($activityLog->action_type == 'add_keyword')
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            Adição de Palavra-chave
                        </span>
                    @elseif($activityLog->action_type == 'add_negative_keyword')
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                            Adição de Palavra-chave Negativa
                        </span>
                    @else
                        {{ $activityLog->action_type }}
                    @endif
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    Realizada em {{ $activityLog->created_at->format('d/m/Y H:i:s') }}
                </p>
            </div>
            <div class="border-t border-gray-200">
                <dl>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            ID
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $activityLog->id }}
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Usuário
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $activityLog->user->name ?? 'N/A' }} ({{ $activityLog->user->email ?? 'N/A' }})
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Tipo de Ação
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            @if($activityLog->action_type == 'add_keyword')
                                Adição de Palavra-chave
                            @elseif($activityLog->action_type == 'add_negative_keyword')
                                Adição de Palavra-chave Negativa
                            @else
                                {{ $activityLog->action_type }}
                            @endif
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Tipo de Entidade
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            @if($activityLog->entity_type == 'keyword')
                                Palavra-chave
                            @elseif($activityLog->entity_type == 'negative_keyword')
                                Palavra-chave Negativa
                            @else
                                {{ $activityLog->entity_type }}
                            @endif
                            @if($activityLog->entity_id)
                                (ID: {{ $activityLog->entity_id }})
                            @endif
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Campanha
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $activityLog->campaign_name ?? 'N/A' }}
                            @if($activityLog->campaign_id)
                                (ID: {{ $activityLog->campaign_id }})
                            @endif
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Grupo de Anúncios
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $activityLog->ad_group_name ?? 'N/A' }}
                            @if($activityLog->ad_group_id)
                                (ID: {{ $activityLog->ad_group_id }})
                            @endif
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Palavra-chave
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            @if(isset($activityLog->details['keyword']))
                                <span class="font-medium">{{ $activityLog->details['keyword'] }}</span>
                                @if(isset($activityLog->details['match_type']))
                                    ({{ strtoupper($activityLog->details['match_type']) }})
                                @endif
                            @else
                                N/A
                            @endif
                        </dd>
                    </div>
                    @if(isset($activityLog->details['reason']))
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Motivo
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $activityLog->details['reason'] }}
                        </dd>
                    </div>
                    @endif
                    @if(isset($activityLog->details['resource_name']))
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Resource Name (Google Ads)
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <code class="text-xs bg-gray-100 p-1 rounded">{{ $activityLog->details['resource_name'] }}</code>
                        </dd>
                    </div>
                    @endif
                    @if(isset($activityLog->details['list_id']))
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            ID da Lista de Palavras-chave Negativas
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $activityLog->details['list_id'] }}
                        </dd>
                    </div>
                    @endif
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Detalhes Completos (JSON)
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <div class="bg-gray-100 p-4 rounded overflow-x-auto">
                                <pre class="text-xs">{{ json_encode($activityLog->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
