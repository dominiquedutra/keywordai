<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem, SidebarSeparator } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, LayoutGrid, Search, Sparkles, Ban, ClipboardList, Settings, Terminal, Key } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Termos de Pesquisa',
        href: '/search-terms',
        icon: Search,
    },
    {
        title: 'Análise IA',
        href: '/ai-analysis',
        icon: Sparkles,
    },
    {
        title: 'Palavras-chave Negativas',
        href: '/negative-keywords',
        icon: Ban,
    },
    {
        title: 'Log de Atividades',
        href: '/activity-logs',
        icon: ClipboardList,
    },
];

const secondaryNavItems: NavItem[] = [
    {
        title: 'Configurações',
        href: '/settings/global',
        icon: Settings,
    },
    {
        title: 'Fila e Comandos',
        href: '/queue-commands',
        icon: Terminal,
    },
    {
        title: 'API Tokens',
        href: '/api-tokens',
        icon: Key,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Docs: API',
        href: '/api/docs',
        icon: BookOpen,
    },
    {
        title: 'Docs: Sistema',
        href: '/docs/sistema',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
            <SidebarSeparator />
            <NavMain :items="secondaryNavItems" label="Administração" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
