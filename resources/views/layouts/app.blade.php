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
    @yield('styles')
</head>
<body class="font-sans antialiased bg-background text-foreground">
    @include('components.sidebar')

    <div class="sidebar-main-content min-h-screen {{ request()->cookie('sidebar_state', 'true') === 'false' ? 'sidebar-collapsed' : '' }}">
        {{-- Top bar with mobile spacer --}}
        <header class="flex h-12 items-center gap-2 border-b px-4 md:px-6">
            <div class="md:hidden w-8"></div>{{-- spacer for mobile hamburger --}}
            <nav class="flex items-center gap-1 text-sm text-muted-foreground">
                @yield('breadcrumb')
            </nav>
        </header>

        <div class="flex flex-1 flex-col p-4 md:p-6">
            @yield('content')
        </div>
    </div>

    <!-- Notificação de status -->
    <div id="notification" class="fixed bottom-4 right-4 px-4 py-2 bg-gray-800 text-white rounded shadow-lg transform transition-opacity duration-300 opacity-0 pointer-events-none z-50">
        <span id="notification-message"></span>
    </div>

    @yield('scripts')
</body>
</html>
