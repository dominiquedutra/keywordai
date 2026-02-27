<script setup lang="ts">
interface Props {
    variant?: 'header' | 'sidebar';
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="flex min-h-screen w-full flex-col">
        <slot />
    </div>
</template>
