<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KeywordAI - @yield('title', 'Dashboard')</title>

    {{-- Inline script to detect system dark mode preference and apply it immediately --}}
    <script>
        (function() {
            const appearance = '{{ $appearance ?? "system" }}';

            if (appearance === 'system') {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                if (prefersDark) {
                    document.documentElement.classList.add('dark');
                }
            }
        })();
    </script>

    {{-- Inline style to set the HTML background color based on our theme in app.css --}}
    <style>
        html {
            background-color: oklch(1 0 0);
        }

        html.dark {
            background-color: oklch(0.145 0 0);
        }
    </style>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    <!-- CSRF Token para requisições AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('styles')
</head>
<body class="font-sans antialiased bg-background text-foreground">
    @include('components.navbar')

    <main class="flex w-full flex-1 flex-col gap-4 p-4 md:p-6">
        @yield('content')
    </main>

    <!-- Notificação de status -->
    <div id="notification" class="fixed bottom-4 right-4 px-4 py-2 bg-gray-800 text-white rounded shadow-lg transform transition-opacity duration-300 opacity-0 pointer-events-none z-50">
        <span id="notification-message"></span>
    </div>

    @yield('scripts')
</body>
</html>
