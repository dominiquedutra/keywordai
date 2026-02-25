<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Palavra-Chave</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6 text-center text-gray-700">Adicionar Palavra-Chave</h1>

        <!-- Display Ad Group Info -->
        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-md">
            <p class="text-sm text-gray-600"><strong class="font-semibold text-gray-700">Grupo de Anúncios Atual:</strong> {{ $ad_group_name ?? 'N/A' }} (ID: {{ $ad_group_id ?? 'N/A' }})</p>
        </div>

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Erro!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Por favor, corrija os erros abaixo:</strong>
                <ul class="mt-2 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('keyword.add.store') }}" method="POST">
            @csrf

            <!-- Search Term Input -->
            <div class="mb-4">
                <label for="search_term" class="block text-sm font-medium text-gray-700 mb-1">Termo de Pesquisa (Palavra-Chave):</label>
                <input 
                    type="text" 
                    id="search_term" 
                    name="search_term" 
                    value="{{ old('search_term', $search_term ?? '') }}" 
                    required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('search_term') border-red-500 @enderror"
                >
                @error('search_term')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Ad Group Selection -->
            <div class="mb-4">
                <label for="ad_group_id" class="block text-sm font-medium text-gray-700 mb-1">Grupo de Anúncios:</label>
                <select 
                    id="ad_group_id" 
                    name="ad_group_id" 
                    required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('ad_group_id') border-red-500 @enderror"
                >
                    @if(isset($ad_groups) && count($ad_groups) > 0)
                        @foreach($ad_groups as $group)
                            <option value="{{ $group['id'] }}" {{ old('ad_group_id', $ad_group_id) == $group['id'] ? 'selected' : '' }}>
                                {{ $group['text'] }}
                            </option>
                        @endforeach
                    @else
                        <option value="{{ $ad_group_id }}" selected>{{ $ad_group_name }}</option>
                    @endif
                </select>
                @error('ad_group_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Match Type Selection -->
            <div class="mb-6">
                <label for="match_type" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Correspondência:</label>
                <select 
                    id="match_type" 
                    name="match_type" 
                    required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('match_type') border-red-500 @enderror"
                >
                    <option value="exact" {{ old('match_type', $default_match_type ?? '') == 'exact' ? 'selected' : '' }}>Exata</option>
                    <option value="phrase" {{ old('match_type', $default_match_type ?? '') == 'phrase' ? 'selected' : '' }}>Frase</option>
                    <option value="broad" {{ old('match_type', $default_match_type ?? '') == 'broad' ? 'selected' : '' }}>Ampla</option>
                </select>
                 @error('match_type')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div>
                <button 
                    type="submit" 
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Adicionar Palavra-Chave
                </button>
            </div>
        </form>
    </div>
</body>
</html>
