<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    variant?: 'header' | 'sidebar';
    class?: string;
}

const props = defineProps<Props>();
const className = computed(() => props.class);
</script>

<template>
    <main class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl" :class="className">
        <slot />
    </main>
</template>
