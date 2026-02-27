<script setup lang="ts">
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <main class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-4 p-4 md:p-6">
        <slot />
    </main>
</template>
