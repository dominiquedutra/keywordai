<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solicitação Enviada</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.ts']) 
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 flex items-center justify-center min-h-screen">
    <div class="container mx-auto p-6 max-w-md text-center">
        <div class="bg-white dark:bg-gray-800 shadow-md rounded px-8 pt-10 pb-8 mb-4">
            <svg class="mx-auto h-12 w-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h1 class="text-2xl font-semibold mt-4 mb-6">Solicitação enviada para processamento!</h1>
            {{-- Mensagem de parágrafo removida para concisão --}}
            
            {{-- Opcional: Mensagem flash, se passada --}}
            @if (session('status'))
                <p class="text-sm text-green-600 dark:text-green-400 mb-6">{{ session('status') }}</p>
            @endif
            
            {{-- Botão e texto de fechar janela removidos --}}
            
        </div>
         <p class="text-center text-gray-500 text-xs mt-4">
            Você pode fechar esta janela.
        </p>
    </div>
</body>
</html>
