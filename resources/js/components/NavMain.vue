<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';

const props = withDefaults(defineProps<{
    items: NavItem[];
    label?: string;
}>(), {
    label: 'Platform',
});

const page = usePage<SharedData>();

function isActive(href: string): boolean {
    const url = page.url.split('?')[0];
    if (href === '/dashboard') return url === '/dashboard';
    return url.startsWith(href);
}
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>{{ label }}</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child :is-active="isActive(item.href)"
                    :tooltip="item.title"
                >
                    <a :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </a>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
