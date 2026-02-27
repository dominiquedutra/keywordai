@php
    $sidebarState = request()->cookie('sidebar_state', 'true');
    $collapsed = $sidebarState === 'false';

    $menuItems = [
        ['label' => 'Dashboard', 'href' => '/dashboard', 'route' => 'dashboard', 'icon' => 'layout-grid'],
        ['label' => 'Termos de Pesquisa', 'href' => '/search-terms', 'route' => 'search-terms.*', 'icon' => 'search'],
        ['label' => 'Análise IA', 'href' => '/ai-analysis', 'route' => 'ai-analysis.*', 'icon' => 'sparkles'],
        ['label' => 'Palavras-chave Negativas', 'href' => '/negative-keywords', 'route' => 'negative-keywords.*', 'icon' => 'ban'],
        ['label' => 'Log de Atividades', 'href' => '/activity-logs', 'route' => 'activity-logs.*', 'icon' => 'clipboard-list'],
    ];

    $secondaryItems = [
        ['label' => 'Configurações', 'href' => '/settings/global', 'route' => 'settings.*', 'icon' => 'settings'],
        ['label' => 'Fila e Comandos', 'href' => '/queue-commands', 'route' => 'queue-commands.*', 'icon' => 'terminal'],
        ['label' => 'API Tokens', 'href' => '/api-tokens', 'route' => 'api.tokens.*', 'icon' => 'key'],
    ];
@endphp

{{-- Desktop Sidebar --}}
<aside
    id="sidebar"
    data-state="{{ $collapsed ? 'collapsed' : 'expanded' }}"
    class="sidebar-component hidden md:flex flex-col fixed inset-y-0 left-0 z-30 border-r transition-all duration-200 ease-in-out"
    style="background: hsl(var(--sidebar-background)); color: hsl(var(--sidebar-foreground)); border-color: hsl(var(--sidebar-border));"
>
    {{-- Header --}}
    <div class="flex h-12 items-center px-3 border-b" style="border-color: hsl(var(--sidebar-border));">
        <a href="/dashboard" class="flex items-center gap-2 overflow-hidden">
            <div class="flex aspect-square size-8 shrink-0 items-center justify-center rounded-md" style="background: hsl(var(--sidebar-primary)); color: hsl(var(--sidebar-primary-foreground));">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 42" class="size-5 fill-current">
                    <path fill="currentColor" fill-rule="evenodd" clip-rule="evenodd" d="M17.2 5.633 8.6.855 0 5.633v26.51l16.2 9 16.2-9v-8.442l7.6-4.223V9.856l-8.6-4.777-8.6 4.777V18.3l-5.6 3.111V5.633ZM38 18.301l-5.6 3.11v-6.157l5.6-3.11V18.3Zm-1.06-7.856-5.54 3.078-5.54-3.079 5.54-3.078 5.54 3.079ZM24.8 18.3v-6.157l5.6 3.111v6.158L24.8 18.3Zm-1 1.732 5.54 3.078-13.14 7.302-5.54-3.078 13.14-7.3v-.002Zm-16.2 7.89 7.6 4.222V38.3L2 30.966V7.92l5.6 3.111v16.892ZM8.6 9.3 3.06 6.222 8.6 3.143l5.54 3.08L8.6 9.3Zm21.8 15.51-13.2 7.334V38.3l13.2-7.334v-6.156ZM9.6 11.034l5.6-3.11v14.6l-5.6 3.11v-14.6Z"/>
                </svg>
            </div>
            <span class="sidebar-label truncate font-semibold text-sm leading-none">KeywordAI</span>
        </a>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto py-2 px-2">
        {{-- Main group --}}
        <div class="mb-1">
            <span class="sidebar-label px-3 py-1.5 text-xs font-medium text-muted-foreground">Platform</span>
            <ul class="space-y-0.5 mt-0.5">
                @foreach($menuItems as $item)
                    @php $active = request()->routeIs($item['route']); @endphp
                    <li>
                        <a
                            href="{{ $item['href'] }}"
                            class="sidebar-link group flex items-center gap-2 rounded-md px-2 py-1.5 text-sm font-medium transition-colors {{ $active ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground' }}"
                            @if($collapsed) title="{{ $item['label'] }}" @endif
                        >
                            <span class="sidebar-icon shrink-0 size-4">
                                @include('components.icons.' . $item['icon'])
                            </span>
                            <span class="sidebar-label truncate">{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Separator --}}
        <div class="my-2 mx-2 h-px" style="background: hsl(var(--sidebar-border));"></div>

        {{-- Secondary group --}}
        <div>
            <span class="sidebar-label px-3 py-1.5 text-xs font-medium text-muted-foreground">Administração</span>
            <ul class="space-y-0.5 mt-0.5">
                @foreach($secondaryItems as $item)
                    @php $active = request()->routeIs($item['route']); @endphp
                    <li>
                        <a
                            href="{{ $item['href'] }}"
                            class="sidebar-link group flex items-center gap-2 rounded-md px-2 py-1.5 text-sm font-medium transition-colors {{ $active ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground' }}"
                            @if($collapsed) title="{{ $item['label'] }}" @endif
                        >
                            <span class="sidebar-icon shrink-0 size-4">
                                @include('components.icons.' . $item['icon'])
                            </span>
                            <span class="sidebar-label truncate">{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </nav>

    {{-- Footer --}}
    <div class="border-t px-2 py-2" style="border-color: hsl(var(--sidebar-border));">
        {{-- Docs links --}}
        <div class="space-y-0.5 mb-2">
            <a href="/api/docs" target="_blank" rel="noopener noreferrer" class="sidebar-link group flex items-center gap-2 rounded-md px-2 py-1.5 text-sm font-medium text-muted-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground transition-colors" @if($collapsed) title="Docs: API" @endif>
                <span class="sidebar-icon shrink-0 size-4">@include('components.icons.book-open')</span>
                <span class="sidebar-label truncate">Docs: API</span>
            </a>
            <a href="/docs/sistema" class="sidebar-link group flex items-center gap-2 rounded-md px-2 py-1.5 text-sm font-medium text-muted-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground transition-colors" @if($collapsed) title="Docs: Sistema" @endif>
                <span class="sidebar-icon shrink-0 size-4">@include('components.icons.book-open')</span>
                <span class="sidebar-label truncate">Docs: Sistema</span>
            </a>
        </div>

        {{-- User info --}}
        <div class="border-t pt-2" style="border-color: hsl(var(--sidebar-border));">
            <div class="flex items-center gap-2 rounded-md px-2 py-1.5">
                <div class="flex aspect-square size-7 shrink-0 items-center justify-center rounded-full bg-muted text-muted-foreground text-xs font-semibold">
                    {{ Auth::check() ? strtoupper(substr(Auth::user()->name, 0, 1)) : 'V' }}
                </div>
                <div class="sidebar-label flex flex-1 flex-col overflow-hidden">
                    <span class="truncate text-sm font-medium">{{ Auth::check() ? Auth::user()->name : 'Visitante' }}</span>
                    <span class="truncate text-xs text-muted-foreground">{{ Auth::check() ? Auth::user()->email : '' }}</span>
                </div>
                @auth
                <form method="POST" action="{{ route('logout') }}" class="sidebar-label">
                    @csrf
                    <button type="submit" class="p-1 rounded hover:bg-sidebar-accent text-muted-foreground hover:text-sidebar-accent-foreground" title="Sair">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    </button>
                </form>
                @endauth
            </div>
        </div>
    </div>

    {{-- Collapse toggle --}}
    <button
        id="sidebar-toggle"
        class="absolute -right-3 top-3 z-40 flex size-6 items-center justify-center rounded-full border bg-background text-muted-foreground shadow-sm hover:bg-accent hover:text-accent-foreground transition-colors"
        title="Alternar barra lateral (Ctrl+B)"
    >
        <svg id="sidebar-toggle-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect width="18" height="18" x="3" y="3" rx="2"/>
            <path d="M9 3v18"/>
        </svg>
    </button>
</aside>

{{-- Mobile: hamburger trigger --}}
<button
    id="mobile-sidebar-trigger"
    class="md:hidden fixed top-3 left-3 z-50 flex size-8 items-center justify-center rounded-md border bg-background text-muted-foreground shadow-sm hover:bg-accent"
>
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
</button>

{{-- Mobile: overlay --}}
<div id="mobile-sidebar-overlay" class="md:hidden fixed inset-0 z-40 bg-black/50 hidden"></div>

{{-- Mobile: drawer --}}
<aside
    id="mobile-sidebar"
    class="md:hidden fixed inset-y-0 left-0 z-50 w-72 transform -translate-x-full transition-transform duration-200 ease-in-out border-r"
    style="background: hsl(var(--sidebar-background)); color: hsl(var(--sidebar-foreground)); border-color: hsl(var(--sidebar-border));"
>
    {{-- Close button --}}
    <button id="mobile-sidebar-close" class="absolute top-3 right-3 p-1 rounded hover:bg-sidebar-accent text-muted-foreground">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
    </button>

    {{-- Header --}}
    <div class="flex h-12 items-center px-3 border-b" style="border-color: hsl(var(--sidebar-border));">
        <a href="/dashboard" class="flex items-center gap-2">
            <div class="flex aspect-square size-8 items-center justify-center rounded-md" style="background: hsl(var(--sidebar-primary)); color: hsl(var(--sidebar-primary-foreground));">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 42" class="size-5 fill-current">
                    <path fill="currentColor" fill-rule="evenodd" clip-rule="evenodd" d="M17.2 5.633 8.6.855 0 5.633v26.51l16.2 9 16.2-9v-8.442l7.6-4.223V9.856l-8.6-4.777-8.6 4.777V18.3l-5.6 3.111V5.633ZM38 18.301l-5.6 3.11v-6.157l5.6-3.11V18.3Zm-1.06-7.856-5.54 3.078-5.54-3.079 5.54-3.078 5.54 3.079ZM24.8 18.3v-6.157l5.6 3.111v6.158L24.8 18.3Zm-1 1.732 5.54 3.078-13.14 7.302-5.54-3.078 13.14-7.3v-.002Zm-16.2 7.89 7.6 4.222V38.3L2 30.966V7.92l5.6 3.111v16.892ZM8.6 9.3 3.06 6.222 8.6 3.143l5.54 3.08L8.6 9.3Zm21.8 15.51-13.2 7.334V38.3l13.2-7.334v-6.156ZM9.6 11.034l5.6-3.11v14.6l-5.6 3.11v-14.6Z"/>
                </svg>
            </div>
            <span class="font-semibold text-sm">KeywordAI</span>
        </a>
    </div>

    {{-- Mobile Nav --}}
    <nav class="flex-1 overflow-y-auto py-2 px-2">
        <div class="mb-1">
            <span class="px-3 py-1.5 text-xs font-medium text-muted-foreground">Platform</span>
            <ul class="space-y-0.5 mt-0.5">
                @foreach($menuItems as $item)
                    @php $active = request()->routeIs($item['route']); @endphp
                    <li>
                        <a href="{{ $item['href'] }}" class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm font-medium transition-colors {{ $active ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground' }}">
                            <span class="size-4">@include('components.icons.' . $item['icon'])</span>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="my-2 mx-2 h-px" style="background: hsl(var(--sidebar-border));"></div>
        <div>
            <span class="px-3 py-1.5 text-xs font-medium text-muted-foreground">Administração</span>
            <ul class="space-y-0.5 mt-0.5">
                @foreach($secondaryItems as $item)
                    @php $active = request()->routeIs($item['route']); @endphp
                    <li>
                        <a href="{{ $item['href'] }}" class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm font-medium transition-colors {{ $active ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground' }}">
                            <span class="size-4">@include('components.icons.' . $item['icon'])</span>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </nav>

    {{-- Mobile Footer --}}
    <div class="border-t px-2 py-2" style="border-color: hsl(var(--sidebar-border));">
        <div class="space-y-0.5 mb-2">
            <a href="/api/docs" target="_blank" class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm font-medium text-muted-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground transition-colors">
                <span class="size-4">@include('components.icons.book-open')</span>
                <span>Docs: API</span>
            </a>
            <a href="/docs/sistema" class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm font-medium text-muted-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground transition-colors">
                <span class="size-4">@include('components.icons.book-open')</span>
                <span>Docs: Sistema</span>
            </a>
        </div>
        <div class="border-t pt-2" style="border-color: hsl(var(--sidebar-border));">
            <div class="flex items-center gap-2 px-2 py-1.5">
                <div class="flex size-7 items-center justify-center rounded-full bg-muted text-muted-foreground text-xs font-semibold">
                    {{ Auth::check() ? strtoupper(substr(Auth::user()->name, 0, 1)) : 'V' }}
                </div>
                <div class="flex flex-1 flex-col overflow-hidden">
                    <span class="truncate text-sm font-medium">{{ Auth::check() ? Auth::user()->name : 'Visitante' }}</span>
                    <span class="truncate text-xs text-muted-foreground">{{ Auth::check() ? Auth::user()->email : '' }}</span>
                </div>
                @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="p-1 rounded hover:bg-sidebar-accent text-muted-foreground hover:text-sidebar-accent-foreground" title="Sair">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    </button>
                </form>
                @endauth
            </div>
        </div>
    </div>
</aside>

<style>
    /* Sidebar widths */
    #sidebar {
        width: 16rem;
    }
    #sidebar[data-state="collapsed"] {
        width: 3rem;
    }

    /* Hide labels when collapsed */
    #sidebar[data-state="collapsed"] .sidebar-label {
        display: none;
    }

    /* Center icons when collapsed */
    #sidebar[data-state="collapsed"] .sidebar-link {
        justify-content: center;
        padding-left: 0;
        padding-right: 0;
    }

    /* Center header when collapsed */
    #sidebar[data-state="collapsed"] .flex.h-12 {
        justify-content: center;
        padding-left: 0;
        padding-right: 0;
    }

    /* Sidebar accent colors via Tailwind utility classes */
    .bg-sidebar-accent { background: hsl(var(--sidebar-accent)); }
    .text-sidebar-accent-foreground { color: hsl(var(--sidebar-accent-foreground)); }
    .text-sidebar-foreground { color: hsl(var(--sidebar-foreground)); }
    .hover\:bg-sidebar-accent:hover { background: hsl(var(--sidebar-accent)); }
    .hover\:text-sidebar-accent-foreground:hover { color: hsl(var(--sidebar-accent-foreground)); }

    /* Main content offset */
    .sidebar-main-content {
        margin-left: 16rem;
        transition: margin-left 0.2s ease-in-out;
    }
    .sidebar-main-content.sidebar-collapsed {
        margin-left: 3rem;
    }

    @media (max-width: 767px) {
        .sidebar-main-content {
            margin-left: 0 !important;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.sidebar-main-content');
    const toggleBtn = document.getElementById('sidebar-toggle');
    const mobileTrigger = document.getElementById('mobile-sidebar-trigger');
    const mobileOverlay = document.getElementById('mobile-sidebar-overlay');
    const mobileSidebar = document.getElementById('mobile-sidebar');
    const mobileClose = document.getElementById('mobile-sidebar-close');

    // Desktop toggle
    if (toggleBtn && sidebar) {
        // Set initial main content class
        if (sidebar.dataset.state === 'collapsed' && mainContent) {
            mainContent.classList.add('sidebar-collapsed');
        }

        toggleBtn.addEventListener('click', function() {
            const isCollapsed = sidebar.dataset.state === 'collapsed';
            sidebar.dataset.state = isCollapsed ? 'expanded' : 'collapsed';

            if (mainContent) {
                mainContent.classList.toggle('sidebar-collapsed', !isCollapsed);
            }

            // Persist to cookie (same key as Vue sidebar)
            document.cookie = 'sidebar_state=' + isCollapsed + '; path=/; max-age=604800';
        });
    }

    // Keyboard shortcut: Ctrl+B / Cmd+B
    document.addEventListener('keydown', function(e) {
        if ((e.metaKey || e.ctrlKey) && e.key === 'b') {
            e.preventDefault();
            if (window.innerWidth >= 768 && toggleBtn) {
                toggleBtn.click();
            } else if (mobileTrigger) {
                mobileTrigger.click();
            }
        }
    });

    // Mobile drawer
    function openMobileDrawer() {
        mobileOverlay.classList.remove('hidden');
        mobileSidebar.classList.remove('-translate-x-full');
    }

    function closeMobileDrawer() {
        mobileSidebar.classList.add('-translate-x-full');
        mobileOverlay.classList.add('hidden');
    }

    if (mobileTrigger) mobileTrigger.addEventListener('click', openMobileDrawer);
    if (mobileOverlay) mobileOverlay.addEventListener('click', closeMobileDrawer);
    if (mobileClose) mobileClose.addEventListener('click', closeMobileDrawer);
});
</script>
