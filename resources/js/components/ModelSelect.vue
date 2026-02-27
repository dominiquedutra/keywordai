<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectSeparator, SelectTrigger } from '@/components/ui/select';
import { computed, ref, watch } from 'vue';

export interface ModelOption {
    id: string;
    name: string;
    inputPrice: string;
    outputPrice: string;
    badge: string;
    badgeColor: string;
}

const props = defineProps<{
    models: ModelOption[];
    id?: string;
}>();

const model = defineModel<string>({ required: true });

const CUSTOM_SENTINEL = '__custom__';
const isCustomMode = ref(false);
const customValue = ref('');

// Determine if the current value matches a known model
const isKnownModel = computed(() => {
    return props.models.some((m) => m.id === model.value);
});

// The value shown in the Select dropdown
const selectValue = computed(() => {
    if (isCustomMode.value) return CUSTOM_SENTINEL;
    if (isKnownModel.value) return model.value;
    // Unknown model set externally — show as custom
    return CUSTOM_SENTINEL;
});

// Initialize custom value if current model is not in the list
if (!isKnownModel.value && model.value) {
    isCustomMode.value = true;
    customValue.value = model.value;
}

function onSelectChange(value: string) {
    if (value === CUSTOM_SENTINEL) {
        isCustomMode.value = true;
        // Keep current model value until user types something
        if (!customValue.value) {
            customValue.value = model.value || '';
        }
    } else {
        isCustomMode.value = false;
        customValue.value = '';
        model.value = value;
    }
}

watch(customValue, (val) => {
    if (isCustomMode.value) {
        model.value = val;
    }
});

// Display label for the trigger (compact: just name + badge)
const triggerLabel = computed(() => {
    const match = props.models.find((m) => m.id === model.value);
    if (match) return match.name;
    if (isCustomMode.value && customValue.value) return customValue.value;
    if (model.value) return model.value;
    return '';
});

const triggerBadge = computed(() => {
    const match = props.models.find((m) => m.id === model.value);
    return match || null;
});

function badgeClasses(color: string): string {
    const map: Record<string, string> = {
        green: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        blue: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        purple: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
        gray: 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400',
    };
    return map[color] || map.gray;
}
</script>

<template>
    <div class="space-y-2">
        <Select :model-value="selectValue" @update:model-value="onSelectChange">
            <SelectTrigger :id="id">
                <span v-if="triggerLabel" class="flex items-center gap-1.5 truncate">
                    <span class="truncate font-mono text-xs">{{ triggerLabel }}</span>
                    <span
                        v-if="triggerBadge"
                        class="inline-flex shrink-0 items-center rounded-full px-1.5 py-0.5 text-[10px] font-medium leading-none"
                        :class="badgeClasses(triggerBadge.badgeColor)"
                    >
                        {{ triggerBadge.badge }}
                    </span>
                </span>
                <span v-else class="text-muted-foreground">Select a model</span>
            </SelectTrigger>
            <SelectContent class="max-h-[300px]">
                <SelectGroup>
                    <SelectLabel class="text-xs text-muted-foreground">Recommended models</SelectLabel>
                    <SelectItem v-for="m in models" :key="m.id" :value="m.id">
                        <div class="flex w-full items-center gap-2">
                            <span class="font-mono text-xs">{{ m.name }}</span>
                            <span class="whitespace-nowrap text-[10px] text-muted-foreground"> ${{ m.inputPrice }} / ${{ m.outputPrice }} </span>
                            <span
                                class="inline-flex items-center whitespace-nowrap rounded-full px-1.5 py-0.5 text-[10px] font-medium leading-none"
                                :class="badgeClasses(m.badgeColor)"
                            >
                                {{ m.badge }}
                            </span>
                        </div>
                    </SelectItem>
                </SelectGroup>
                <SelectSeparator />
                <SelectGroup>
                    <SelectItem :value="CUSTOM_SENTINEL"> Custom model ID... </SelectItem>
                </SelectGroup>
            </SelectContent>
        </Select>

        <Input v-if="isCustomMode" v-model="customValue" placeholder="Enter model ID (e.g. gpt-4o)" class="text-sm" />
    </div>
</template>
