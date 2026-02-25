@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Logs de Atividade</h1>
            <p class="mt-1 text-sm text-gray-600">Registro de ações realizadas pelos usuários no sistema.</p>
        </div>

        <!-- Filtros -->
        <div class="bg-white shadow rounded-lg mb-6 p-4">
            <form action="{{ route('activity-logs.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Filtro por Tipo de Ação -->
                    <div>
                        <label for="action_type" class="block text-sm font-medium text-gray-700">Tipo de Ação</label>
                        <select id="action_type" name="action_type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">Todos</option>
                            @foreach($actionTypes as $actionType)
                                <option value="{{ $actionType }}" {{ $filters['action_type'] ?? '' == $actionType ? 'selected' : '' }}>
                                    @if($actionType == 'add_keyword')
                                        Adição de Palavra-chave
                                    @elseif($actionType == 'add_negative_keyword')
                                        Adição de Palavra-chave Negativa
                                    @else
                                        {{ $actionType }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro por Tipo de Entidade -->
                    <div>
                        <label for="entity_type" class="block text-sm font-medium text-gray-700">Tipo de Entidade</label>
                        <select id="entity_type" name="entity_type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">Todos</option>
                            @foreach($entityTypes as $entityType)
                                <option value="{{ $entityType }}" {{ $filters['entity_type'] ?? '' == $entityType ? 'selected' : '' }}>
                                    @if($entityType == 'keyword')
                                        Palavra-chave
                                    @elseif($entityType == 'negative_keyword')
                                        Palavra-chave Negativa
                                    @else
                                        {{ $entityType }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro por Usuário -->
                    <div>
                        <label for="user_id" class="block text-sm font-medium text-gray-700">Usuário</label>
                        <select id="user_id" name="user_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">Todos</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $filters['user_id'] ?? '' == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro por Termo de Busca -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700">Buscar</label>
                        <input type="text" name="search" id="search" value="{{ $filters['search'] ?? '' }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Termo, campanha, grupo...">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Filtro por Data Inicial -->
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Data Inicial</label>
                        <input type="date" name="start_date" id="start_date" value="{{ $filters['start_date'] ?? '' }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>

                    <!-- Filtro por Data Final -->
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">Data Final</label>
                        <input type="date" name="end_date" id="end_date" value="{{ $filters['end_date'] ?? '' }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>

                    <!-- Ordenação -->
                    <div>
                        <label for="sort_by" class="block text-sm font-medium text-gray-700">Ordenar por</label>
                        <select id="sort_by" name="sort_by" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="created_at" {{ ($filters['sort_by'] ?? 'created_at') == 'created_at' ? 'selected' : '' }}>Data</option>
                            <option value="action_type" {{ ($filters['sort_by'] ?? '') == 'action_type' ? 'selected' : '' }}>Tipo de Ação</option>
                            <option value="entity_type" {{ ($filters['sort_by'] ?? '') == 'entity_type' ? 'selected' : '' }}>Tipo de Entidade</option>
                            <option value="user_id" {{ ($filters['sort_by'] ?? '') == 'user_id' ? 'selected' : '' }}>Usuário</option>
                        </select>
                    </div>

                    <!-- Direção da Ordenação -->
                    <div>
                        <label for="sort_direction" class="block text-sm font-medium text-gray-700">Direção</label>
                        <select id="sort_direction" name="sort_direction" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="desc" {{ ($filters['sort_direction'] ?? 'desc') == 'desc' ? 'selected' : '' }}>Decrescente</option>
                            <option value="asc" {{ ($filters['sort_direction'] ?? '') == 'asc' ? 'selected' : '' }}>Crescente</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end">
                    <a href="{{ route('activity-logs.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-2">
                        Limpar Filtros
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Filtrar
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabela de Logs -->
        <div class="bg-white shadow overflow-hidden rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Data/Hora
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Usuário
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ação
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Entidade
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Campanha
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Grupo de Anúncios
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Detalhes
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($activityLogs as $log)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $log->user->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($log->action_type == 'add_keyword')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Adição de Palavra-chave
                                        </span>
                                    @elseif($log->action_type == 'add_negative_keyword')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Adição de Palavra-chave Negativa
                                        </span>
                                    @else
                                        {{ $log->action_type }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($log->entity_type == 'keyword')
                                        Palavra-chave
                                    @elseif($log->entity_type == 'negative_keyword')
                                        Palavra-chave Negativa
                                    @else
                                        {{ $log->entity_type }}
                                    @endif
                                    @if($log->entity_id)
                                        #{{ $log->entity_id }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $log->campaign_name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $log->ad_group_name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if(isset($log->details['keyword']))
                                        <span class="font-medium">{{ $log->details['keyword'] }}</span>
                                        @if(isset($log->details['match_type']))
                                            ({{ strtoupper($log->details['match_type']) }})
                                        @endif
                                    @endif
                                    @if(isset($log->details['reason']))
                                        <br><span class="text-xs text-gray-500">Motivo: {{ $log->details['reason'] }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('activity-logs.show', $log) }}" class="text-indigo-600 hover:text-indigo-900">Detalhes</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    Nenhum log de atividade encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $activityLogs->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
