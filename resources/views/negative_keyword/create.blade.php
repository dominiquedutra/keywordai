<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Adicionar Palavra-Chave Negativa</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.ts']) 
    {{-- Assume que Vite está configurado para compilar app.css que inclui Tailwind --}}
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200">
    <div class="container mx-auto p-6 max-w-lg">
        <h1 class="text-2xl font-semibold mb-6 text-center">Adicionar Palavra-Chave Negativa</h1>

        {{-- Exibir mensagens de erro de validação --}}
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Erro!</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Exibir mensagem de erro geral (do controller) --}}
         @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Erro!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 shadow-md rounded px-8 pt-6 pb-8 mb-4">
            {{-- Remover este bloco duplicado do termo --}}

            {{-- Restaurar exibição das informações --}}
             <div class="mb-4">
                 <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">
                     Campanha:
                 </label>
                 <p class="text-gray-600 dark:text-gray-400">{{ $campaignName ?? 'N/A' }}</p>
             </div>

             <div class="mb-4">
                 <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">
                     Grupo de Anúncios:
                 </label>
                 <p class="text-gray-600 dark:text-gray-400">{{ $adGroupName ?? 'N/A' }}</p>
             </div>

             <div class="mb-6">
                 <label class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">
                     Palavra-Chave Original:
                 </label>
                 <p class="text-gray-600 dark:text-gray-400">{{ $keywordText ?? 'N/A' }}</p>
             </div> 

            <hr class="mb-6 border-gray-300 dark:border-gray-600">

            <form action="{{ route('negative-keyword.store') }}" method="POST">
                @csrf {{-- Token CSRF --}}

                 {{-- COLOCAR O CAMPO TERM AQUI DENTRO --}}
                 <div class="mb-4">
                    <label for="term" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">
                        Termo de Pesquisa (Editável):
                    </label>
                    <input type="text" 
                           id="term" 
                           name="term" 
                           value="{{ old('term', $term ?? '') }}" 
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('term') border-red-500 @enderror" 
                           required>
                     @error('term')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                     @enderror
                </div>

                {{-- Campo oculto para o ID da lista (manter) --}}
                <input type="hidden" name="list_id" value="{{ $listId ?? '' }}">

                <div class="mb-4">
                    <label for="match_type" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">
                        Tipo de Correspondência para Negativação:
                    </label>
                    <select name="match_type" id="match_type" class="shadow border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('match_type') border-red-500 @enderror">
                        <option value="broad" {{ old('match_type', $matchType ?? '') == 'broad' ? 'selected' : '' }}>Ampla (Broad)</option>
                        <option value="phrase" {{ old('match_type', $matchType ?? '') == 'phrase' ? 'selected' : '' }}>Frase (Phrase)</option>
                        <option value="exact" {{ old('match_type', $matchType ?? '') == 'exact' ? 'selected' : '' }}>Exata (Exact)</option>
                    </select>
                    @error('match_type')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Novo campo para o motivo da negativação --}}
                <div class="mb-6">
                    <label for="reason" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">
                        Motivo da Negativação:
                    </label>
                    <textarea 
                        id="reason" 
                        name="reason" 
                        rows="3" 
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('reason') border-red-500 @enderror"
                        placeholder="Explique por que esta palavra-chave deve ser negativada (opcional)">{{ old('reason', '') }}</textarea>
                    <p class="text-gray-500 text-xs mt-1">Este campo será usado para construir nossa base de conhecimento para avaliação automática de termos de pesquisa no futuro.</p>
                    @error('reason')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Futuramente: Adicionar selector para nível (Global, Campanha, AdGroup) --}}
                {{-- <div class="mb-6">
                    <label for="level" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">
                        Nível de Negativação:
                    </label>
                    <select name="level" id="level" class="shadow border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="global" selected>Global (Lista Padrão: {{ $listId ?? 'N/A' }})</option>
                        <option value="campaign" disabled>Campanha (Não implementado)</option>
                        <option value="adgroup" disabled>Grupo de Anúncios (Não implementado)</option>
                    </select>
                </div> --}}


                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Salvar Negativação na Fila
                    </button>
                    {{-- Adicionar um botão de cancelar, se desejado --}}
                     <a href="{{ url()->previous() ?? route('home') }}" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
