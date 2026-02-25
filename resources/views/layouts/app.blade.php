<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KeywordAI - @yield('title', 'Dashboard')</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    <!-- CSRF Token para requisições AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
    @include('components.main-navigation')
    <div class="px-4 py-8">
        @yield('content')
    </div>

    <!-- Notificação de status -->
    <div id="notification" class="fixed bottom-4 right-4 px-4 py-2 bg-gray-800 text-white rounded shadow-lg transform transition-opacity duration-300 opacity-0 pointer-events-none">
        <span id="notification-message"></span>
    </div>
    
    @yield('scripts')
</body>
</html>
