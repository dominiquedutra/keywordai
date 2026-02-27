<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistema') — KeywordAI Docs</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    <style>
        .code-block {
            @apply bg-gray-900 text-gray-100 rounded-md p-4 overflow-x-auto text-sm font-mono;
        }

        .nav-link {
            @apply block px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800 rounded-md;
        }

        .nav-link.active {
            @apply text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 font-medium;
        }

        html { scroll-behavior: smooth; }

        .copy-btn {
            @apply absolute top-2 right-2 px-2 py-1 text-xs bg-gray-700 text-gray-300 rounded hover:bg-gray-600 transition-colors;
        }
    </style>
    @stack('styles')
</head>
<body class="font-sans antialiased bg-white dark:bg-gray-900">
    <!-- Header -->
    <header class="sticky top-0 z-50 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="{{ route('docs.sistema.index') }}" class="flex items-center">
                        <svg class="h-8 w-8 text-indigo-600" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                        <span class="ml-2 text-xl font-bold text-gray-900 dark:text-white">KeywordAI Sistema</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/api/docs" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">API Docs</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            Voltar ao App
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">Login</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="lg:grid lg:grid-cols-12 lg:gap-8">
            <!-- Sidebar Navigation -->
            <div class="hidden lg:block lg:col-span-3">
                <nav class="sticky top-24 overflow-y-auto h-[calc(100vh-8rem)] pr-4">
                    <h5 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider">Sistema</h5>
                    <ul class="space-y-1 mb-6">
                        <li>
                            <a href="{{ route('docs.sistema.index') }}"
                               class="nav-link {{ request()->routeIs('docs.sistema.index') ? 'active' : '' }}">
                                Visao Geral
                            </a>
                        </li>
                    </ul>

                    <h5 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider">Sincronizacao</h5>
                    <ul class="space-y-1 mb-6">
                        <li>
                            <a href="{{ route('docs.sistema.batch-stats-sync') }}"
                               class="nav-link {{ request()->routeIs('docs.sistema.batch-stats-sync') ? 'active' : '' }}">
                                Batch Stats Sync
                            </a>
                        </li>
                    </ul>

                    @yield('sidebar-extra')
                </nav>
            </div>

            <!-- Main Content -->
            <main class="lg:col-span-9">
                @yield('docs-content')

                <!-- Footer -->
                <footer class="border-t border-gray-200 dark:border-gray-800 pt-8 mt-12">
                    <p class="text-center text-gray-500 dark:text-gray-400 text-sm">
                        KeywordAI Sistema Documentation &copy; {{ date('Y') }}
                    </p>
                </footer>
            </main>
        </div>
    </div>

    <script>
        function copyCode(btn) {
            const code = btn.previousElementSibling.querySelector('code').textContent;
            navigator.clipboard.writeText(code).then(() => {
                const original = btn.textContent;
                btn.textContent = 'Copiado!';
                setTimeout(() => btn.textContent = original, 2000);
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
