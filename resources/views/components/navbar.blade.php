@php
    $gestaoItems = [
        ['label' => 'Termos de Pesquisa', 'href' => '/search-terms', 'route' => 'search-terms.*', 'icon' => 'search'],
        ['label' => 'Análise IA', 'href' => '/ai-analysis', 'route' => 'ai-analysis.*', 'icon' => 'sparkles'],
        ['label' => 'Negativas', 'href' => '/negative-keywords', 'route' => 'negative-keywords.*', 'icon' => 'ban'],
    ];

    $monitoramentoItems = [
        ['label' => 'Log de Atividades', 'href' => '/activity-logs', 'route' => 'activity-logs.*', 'icon' => 'clipboard-list'],
        ['label' => 'Log de Análise IA', 'href' => '/ai-analysis-logs', 'route' => 'ai-analysis-logs.*', 'icon' => 'brain'],
        ['label' => 'Fila e Comandos', 'href' => '/queue-commands', 'route' => 'queue-commands.*', 'icon' => 'terminal'],
    ];

    $adminItems = [
        ['label' => 'Configurações', 'href' => '/settings/global', 'route' => 'settings.*', 'icon' => 'settings'],
        ['label' => 'API Tokens', 'href' => '/api-tokens', 'route' => 'api.tokens.*', 'icon' => 'key'],
    ];

    $adminDocsItems = [
        ['label' => 'Docs: API', 'href' => '/api/docs', 'icon' => 'book-open', 'external' => true],
        ['label' => 'Docs: Sistema', 'href' => '/docs/sistema', 'icon' => 'book-open'],
    ];

    $gestaoActive = collect($gestaoItems)->contains(fn($i) => request()->routeIs($i['route']));
    $monitoramentoActive = collect($monitoramentoItems)->contains(fn($i) => request()->routeIs($i['route']));
    $adminActive = collect($adminItems)->contains(fn($i) => request()->routeIs($i['route']));
@endphp

@auth
<nav id="main-navbar" class="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80">
    <div class="flex h-14 items-center px-4 md:px-6">
        {{-- Logo --}}
        <a href="/dashboard" class="mr-6 flex shrink-0 items-center gap-2">
            <div class="flex size-7 items-center justify-center rounded-md bg-primary text-primary-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 42" class="size-4 fill-current">
                    <path fill="currentColor" fill-rule="evenodd" clip-rule="evenodd" d="M17.2 5.633 8.6.855 0 5.633v26.51l16.2 9 16.2-9v-8.442l7.6-4.223V9.856l-8.6-4.777-8.6 4.777V18.3l-5.6 3.111V5.633ZM38 18.301l-5.6 3.11v-6.157l5.6-3.11V18.3Zm-1.06-7.856-5.54 3.078-5.54-3.079 5.54-3.078 5.54 3.079ZM24.8 18.3v-6.157l5.6 3.111v6.158L24.8 18.3Zm-1 1.732 5.54 3.078-13.14 7.302-5.54-3.078 13.14-7.3v-.002Zm-16.2 7.89 7.6 4.222V38.3L2 30.966V7.92l5.6 3.111v16.892ZM8.6 9.3 3.06 6.222 8.6 3.143l5.54 3.08L8.6 9.3Zm21.8 15.51-13.2 7.334V38.3l13.2-7.334v-6.156ZM9.6 11.034l5.6-3.11v14.6l-5.6 3.11v-14.6Z"/>
                </svg>
            </div>
            <span class="hidden text-sm font-semibold sm:inline-block">KeywordAI</span>
        </a>

        {{-- Desktop navigation --}}
        <div class="hidden md:flex md:flex-1 md:items-center md:gap-1">
            {{-- Dashboard (direct link) --}}
            <a href="/dashboard" class="rounded-md px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-accent text-accent-foreground' : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground' }}">
                Dashboard
            </a>

            {{-- Gestão dropdown --}}
            <div class="navbar-dropdown relative">
                <button type="button" class="navbar-dropdown-trigger inline-flex items-center gap-1 rounded-md px-3 py-2 text-sm font-medium transition-colors {{ $gestaoActive ? 'bg-accent text-accent-foreground' : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground' }}">
                    Gestão
                    <svg class="navbar-chevron size-3.5 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div class="navbar-dropdown-menu absolute left-0 top-full z-50 mt-1 hidden min-w-[200px] rounded-md border bg-popover p-1 shadow-md">
                    @foreach($gestaoItems as $item)
                        @php $active = request()->routeIs($item['route']); @endphp
                        <a href="{{ $item['href'] }}" class="flex items-center gap-2 rounded-sm px-2.5 py-2 text-sm transition-colors {{ $active ? 'bg-accent text-accent-foreground' : 'text-popover-foreground hover:bg-accent hover:text-accent-foreground' }}">
                            <span class="size-4 shrink-0 text-muted-foreground">@include('components.icons.' . $item['icon'])</span>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Monitoramento dropdown --}}
            <div class="navbar-dropdown relative">
                <button type="button" class="navbar-dropdown-trigger inline-flex items-center gap-1 rounded-md px-3 py-2 text-sm font-medium transition-colors {{ $monitoramentoActive ? 'bg-accent text-accent-foreground' : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground' }}">
                    Monitoramento
                    <svg class="navbar-chevron size-3.5 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div class="navbar-dropdown-menu absolute left-0 top-full z-50 mt-1 hidden min-w-[200px] rounded-md border bg-popover p-1 shadow-md">
                    @foreach($monitoramentoItems as $item)
                        @php $active = request()->routeIs($item['route']); @endphp
                        <a href="{{ $item['href'] }}" class="flex items-center gap-2 rounded-sm px-2.5 py-2 text-sm transition-colors {{ $active ? 'bg-accent text-accent-foreground' : 'text-popover-foreground hover:bg-accent hover:text-accent-foreground' }}">
                            <span class="size-4 shrink-0 text-muted-foreground">@include('components.icons.' . $item['icon'])</span>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Admin dropdown --}}
            <div class="navbar-dropdown relative">
                <button type="button" class="navbar-dropdown-trigger inline-flex items-center gap-1 rounded-md px-3 py-2 text-sm font-medium transition-colors {{ $adminActive ? 'bg-accent text-accent-foreground' : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground' }}">
                    Admin
                    <svg class="navbar-chevron size-3.5 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                <div class="navbar-dropdown-menu absolute left-0 top-full z-50 mt-1 hidden min-w-[200px] rounded-md border bg-popover p-1 shadow-md">
                    @foreach($adminItems as $item)
                        @php $active = request()->routeIs($item['route']); @endphp
                        <a href="{{ $item['href'] }}" class="flex items-center gap-2 rounded-sm px-2.5 py-2 text-sm transition-colors {{ $active ? 'bg-accent text-accent-foreground' : 'text-popover-foreground hover:bg-accent hover:text-accent-foreground' }}">
                            <span class="size-4 shrink-0 text-muted-foreground">@include('components.icons.' . $item['icon'])</span>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                    <div class="my-1 h-px bg-border"></div>
                    @foreach($adminDocsItems as $item)
                        <a href="{{ $item['href'] }}" @if(!empty($item['external'])) target="_blank" rel="noopener noreferrer" @endif class="flex items-center gap-2 rounded-sm px-2.5 py-2 text-sm text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground">
                            <span class="size-4 shrink-0">@include('components.icons.' . $item['icon'])</span>
                            {{ $item['label'] }}
                            @if(!empty($item['external']))
                                <svg class="ml-auto size-3 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Right side: user menu (desktop) --}}
        <div class="hidden md:flex md:items-center md:gap-2">
            @auth
                <div class="navbar-dropdown relative">
                    <button type="button" class="navbar-dropdown-trigger inline-flex items-center gap-2 rounded-md px-2 py-1.5 text-sm transition-colors hover:bg-accent">
                        <div class="flex size-7 items-center justify-center rounded-full bg-primary text-primary-foreground text-xs font-semibold">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <span class="max-w-[120px] truncate text-sm font-medium">{{ Auth::user()->name }}</span>
                        <svg class="navbar-chevron size-3.5 text-muted-foreground transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div class="navbar-dropdown-menu absolute right-0 top-full z-50 mt-1 hidden min-w-[200px] rounded-md border bg-popover p-1 shadow-md">
                        <div class="px-2.5 py-2 text-xs text-muted-foreground">{{ Auth::user()->email }}</div>
                        <div class="my-1 h-px bg-border"></div>
                        <a href="/settings/profile" class="flex items-center gap-2 rounded-sm px-2.5 py-2 text-sm text-popover-foreground transition-colors hover:bg-accent hover:text-accent-foreground">
                            <span class="size-4 shrink-0 text-muted-foreground">@include('components.icons.settings')</span>
                            Configurações
                        </a>
                        <div class="my-1 h-px bg-border"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2 rounded-sm px-2.5 py-2 text-sm text-popover-foreground transition-colors hover:bg-accent hover:text-accent-foreground">
                                <svg class="size-4 shrink-0 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                Sair
                            </button>
                        </form>
                    </div>
                </div>
            @endauth
        </div>

        {{-- Mobile hamburger --}}
        <button id="navbar-mobile-toggle" type="button" class="ml-auto inline-flex items-center justify-center rounded-md p-2 text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground md:hidden">
            <svg id="navbar-hamburger-icon" class="size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
            <svg id="navbar-close-icon" class="hidden size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>
    </div>

    {{-- Mobile panel --}}
    <div id="navbar-mobile-panel" class="hidden border-t md:hidden">
        <div class="space-y-1 px-4 py-3">
            {{-- Dashboard --}}
            <a href="/dashboard" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-accent text-accent-foreground' : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground' }}">
                <span class="size-4 shrink-0">@include('components.icons.layout-grid')</span>
                Dashboard
            </a>

            {{-- Gestão group --}}
            <div class="pt-2">
                <span class="px-3 text-xs font-semibold uppercase tracking-wider text-muted-foreground/70">Gestão</span>
                <div class="mt-1 space-y-0.5">
                    @foreach($gestaoItems as $item)
                        @php $active = request()->routeIs($item['route']); @endphp
                        <a href="{{ $item['href'] }}" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors {{ $active ? 'bg-accent text-accent-foreground' : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground' }}">
                            <span class="size-4 shrink-0">@include('components.icons.' . $item['icon'])</span>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Monitoramento group --}}
            <div class="pt-2">
                <span class="px-3 text-xs font-semibold uppercase tracking-wider text-muted-foreground/70">Monitoramento</span>
                <div class="mt-1 space-y-0.5">
                    @foreach($monitoramentoItems as $item)
                        @php $active = request()->routeIs($item['route']); @endphp
                        <a href="{{ $item['href'] }}" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors {{ $active ? 'bg-accent text-accent-foreground' : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground' }}">
                            <span class="size-4 shrink-0">@include('components.icons.' . $item['icon'])</span>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Admin group --}}
            <div class="pt-2">
                <span class="px-3 text-xs font-semibold uppercase tracking-wider text-muted-foreground/70">Admin</span>
                <div class="mt-1 space-y-0.5">
                    @foreach($adminItems as $item)
                        @php $active = request()->routeIs($item['route']); @endphp
                        <a href="{{ $item['href'] }}" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors {{ $active ? 'bg-accent text-accent-foreground' : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground' }}">
                            <span class="size-4 shrink-0">@include('components.icons.' . $item['icon'])</span>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                    <div class="my-1 mx-3 h-px bg-border"></div>
                    @foreach($adminDocsItems as $item)
                        <a href="{{ $item['href'] }}" @if(!empty($item['external'])) target="_blank" rel="noopener noreferrer" @endif class="flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground">
                            <span class="size-4 shrink-0">@include('components.icons.' . $item['icon'])</span>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- User section (mobile) --}}
            @auth
                <div class="mt-2 border-t pt-3">
                    <div class="flex items-center gap-2 px-3 py-1">
                        <div class="flex size-7 items-center justify-center rounded-full bg-primary text-primary-foreground text-xs font-semibold">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="flex flex-col overflow-hidden">
                            <span class="truncate text-sm font-medium">{{ Auth::user()->name }}</span>
                            <span class="truncate text-xs text-muted-foreground">{{ Auth::user()->email }}</span>
                        </div>
                    </div>
                    <div class="mt-1 space-y-0.5">
                        <a href="/settings/profile" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground">
                            <span class="size-4 shrink-0">@include('components.icons.settings')</span>
                            Configurações
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground">
                                <svg class="size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                Sair
                            </button>
                        </form>
                    </div>
                </div>
            @endauth
        </div>
    </div>
</nav>
@endauth

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Desktop dropdown logic ---
    const dropdowns = document.querySelectorAll('.navbar-dropdown');

    dropdowns.forEach(function(dropdown) {
        const trigger = dropdown.querySelector('.navbar-dropdown-trigger');
        const menu = dropdown.querySelector('.navbar-dropdown-menu');
        const chevron = dropdown.querySelector('.navbar-chevron');

        if (!trigger || !menu) return;

        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = !menu.classList.contains('hidden');

            // Close all other dropdowns first
            dropdowns.forEach(function(other) {
                const otherMenu = other.querySelector('.navbar-dropdown-menu');
                const otherChevron = other.querySelector('.navbar-chevron');
                if (other !== dropdown && otherMenu) {
                    otherMenu.classList.add('hidden');
                    if (otherChevron) otherChevron.classList.remove('rotate-180');
                }
            });

            menu.classList.toggle('hidden');
            if (chevron) chevron.classList.toggle('rotate-180');
        });
    });

    // Close dropdowns on click outside
    document.addEventListener('click', function() {
        dropdowns.forEach(function(dropdown) {
            const menu = dropdown.querySelector('.navbar-dropdown-menu');
            const chevron = dropdown.querySelector('.navbar-chevron');
            if (menu) menu.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
        });
    });

    // Close dropdowns on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            dropdowns.forEach(function(dropdown) {
                const menu = dropdown.querySelector('.navbar-dropdown-menu');
                const chevron = dropdown.querySelector('.navbar-chevron');
                if (menu) menu.classList.add('hidden');
                if (chevron) chevron.classList.remove('rotate-180');
            });
        }
    });

    // --- Mobile panel logic ---
    var mobileToggle = document.getElementById('navbar-mobile-toggle');
    var mobilePanel = document.getElementById('navbar-mobile-panel');
    var hamburgerIcon = document.getElementById('navbar-hamburger-icon');
    var closeIcon = document.getElementById('navbar-close-icon');

    if (mobileToggle && mobilePanel) {
        mobileToggle.addEventListener('click', function() {
            var isOpen = !mobilePanel.classList.contains('hidden');
            mobilePanel.classList.toggle('hidden');
            if (hamburgerIcon) hamburgerIcon.classList.toggle('hidden');
            if (closeIcon) closeIcon.classList.toggle('hidden');
        });
    }
});
</script>
