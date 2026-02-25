@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Palavras-chave Negativas</h1>
            <p class="mt-1 text-sm text-gray-600">Lista de palavras-chave negativas adicionadas ao sistema.</p>
        </div>

        <!-- Filtros -->
        <div class="bg-white shadow rounded-lg mb-6 p-4">
            <form action="{{ route('negative-keywords.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Filtro por Palavra-chave -->
                    <div>
                        <label for="keyword" class="block text-sm font-medium text-gray-700">Palavra-chave</label>
                        <input type="text" name="keyword" id="keyword" value="{{ $filters['keyword'] ?? '' }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Buscar palavra-chave...">
                    </div>

                    <!-- Filtro por Tipo de Correspondência -->
                    <div>
                        <label for="match_type" class="block text-sm font-medium text-gray-700">Tipo de Correspondência</label>
                        <select id="match_type" name="match_type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">Todos</option>
                            @foreach($matchTypes as $matchType)
                                <option value="{{ $matchType }}" {{ $filters['match_type'] ?? '' == $matchType ? 'selected' : '' }}>
                                    @if($matchType == 'broad')
                                        Ampla (Broad)
                                    @elseif($matchType == 'phrase')
                                        Frase (Phrase)
                                    @elseif($matchType == 'exact')
                                        Exata (Exact)
                                    @else
                                        {{ $matchType }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro por Usuário -->
                    <div>
                        <label for="user_id" class="block text-sm font-medium text-gray-700">Adicionado por</label>
                        <select id="user_id" name="user_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">Todos</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $filters['user_id'] ?? '' == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro por Motivo -->
                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700">Motivo</label>
                        <input type="text" name="reason" id="reason" value="{{ $filters['reason'] ?? '' }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Buscar no motivo...">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4">
                    <!-- Ordenação -->
                    <div>
                        <label for="sort_by" class="block text-sm font-medium text-gray-700">Ordenar por</label>
                        <select id="sort_by" name="sort_by" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="created_at" {{ ($filters['sort_by'] ?? 'created_at') == 'created_at' ? 'selected' : '' }}>Data de Criação</option>
                            <option value="keyword" {{ ($filters['sort_by'] ?? '') == 'keyword' ? 'selected' : '' }}>Palavra-chave</option>
                            <option value="match_type" {{ ($filters['sort_by'] ?? '') == 'match_type' ? 'selected' : '' }}>Tipo de Correspondência</option>
                            <option value="created_by_id" {{ ($filters['sort_by'] ?? '') == 'created_by_id' ? 'selected' : '' }}>Usuário</option>
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

                <div class="flex justify-between">
                    <a href="{{ route('negative-keyword.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Adicionar Nova Palavra-chave Negativa
                    </a>

                    <div class="flex">
                        <a href="{{ route('negative-keywords.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-2">
                            Limpar Filtros
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabela de Palavras-chave Negativas -->
        <div class="bg-white shadow overflow-hidden rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Palavra-chave
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tipo de Correspondência
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Motivo
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Lista
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Adicionado por
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Data de Criação
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($negativeKeywords as $negativeKeyword)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $negativeKeyword->keyword }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($negativeKeyword->match_type == 'broad')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Ampla (Broad)
                                        </span>
                                    @elseif($negativeKeyword->match_type == 'phrase')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Frase (Phrase)
                                        </span>
                                    @elseif($negativeKeyword->match_type == 'exact')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Exata (Exact)
                                        </span>
                                    @else
                                        {{ $negativeKeyword->match_type }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                    {{ $negativeKeyword->reason ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $negativeKeyword->list_id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $negativeKeyword->createdBy->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $negativeKeyword->created_at->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('negative-keywords.show', $negativeKeyword) }}" class="text-indigo-600 hover:text-indigo-900">Detalhes</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    Nenhuma palavra-chave negativa encontrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $negativeKeywords->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
