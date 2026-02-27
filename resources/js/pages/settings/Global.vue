<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import ModelSelect, { type ModelOption } from '@/components/ModelSelect.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';

interface SummaryMeta {
    generated_at: string;
    keyword_count: number;
    model_used: string;
    summary_size_bytes: number;
    duration_seconds: number;
    prompt_tokens: number | null;
    completion_tokens: number | null;
}

interface Settings {
    default_keyword_match_type: string;
    default_negative_keyword_match_type: string;
    ai_default_model: string;
    ai_gemini_model: string;
    ai_openai_model: string;
    ai_openrouter_model: string;
    ai_global_custom_instructions: string;
    ai_gemini_custom_instructions: string;
    ai_openai_custom_instructions: string;
    ai_openrouter_custom_instructions: string;
    ai_api_timeout: string;
    has_gemini_key: boolean;
    has_openai_key: boolean;
    has_openrouter_key: boolean;
    ai_summary_model: string;
    ai_summary_model_name: string;
    ai_gemini_max_output_tokens: string;
    negatives_summary: string;
    negatives_summary_meta: SummaryMeta | string | null;
    negatives_summary_stale: boolean;
}

const props = defineProps<{
    settings: Settings;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Global settings',
        href: '/settings/global',
    },
];

const form = useForm({
    default_keyword_match_type: props.settings.default_keyword_match_type,
    default_negative_keyword_match_type: props.settings.default_negative_keyword_match_type,
    ai_default_model: props.settings.ai_default_model,
    ai_gemini_api_key: '',
    ai_gemini_model: props.settings.ai_gemini_model,
    ai_openai_api_key: '',
    ai_openai_model: props.settings.ai_openai_model,
    ai_openrouter_api_key: '',
    ai_openrouter_model: props.settings.ai_openrouter_model,
    ai_global_custom_instructions: props.settings.ai_global_custom_instructions,
    ai_gemini_custom_instructions: props.settings.ai_gemini_custom_instructions,
    ai_openai_custom_instructions: props.settings.ai_openai_custom_instructions,
    ai_openrouter_custom_instructions: props.settings.ai_openrouter_custom_instructions,
    ai_api_timeout: props.settings.ai_api_timeout,
    ai_summary_model: props.settings.ai_summary_model,
    ai_summary_model_name: props.settings.ai_summary_model_name,
    ai_gemini_max_output_tokens: props.settings.ai_gemini_max_output_tokens,
});

const submit = () => {
    form.patch(route('settings.global.update'), {
        preserveScroll: true,
    });
};

// --- Curated model lists ---

const geminiModels: ModelOption[] = [
    {
        id: 'gemini-2.5-flash-lite',
        name: 'gemini-2.5-flash-lite',
        inputPrice: '0.10',
        outputPrice: '0.40',
        badge: 'Cheapest',
        badgeColor: 'green',
    },
    { id: 'gemini-2.5-flash', name: 'gemini-2.5-flash', inputPrice: '0.15', outputPrice: '0.60', badge: '⚡ Fast', badgeColor: 'blue' },
    { id: 'gemini-2.5-pro', name: 'gemini-2.5-pro', inputPrice: '1.25', outputPrice: '10.00', badge: 'Precise', badgeColor: 'purple' },
    { id: 'gemini-2.0-flash', name: 'gemini-2.0-flash', inputPrice: '0.10', outputPrice: '0.40', badge: 'Legacy', badgeColor: 'gray' },
];

const openaiModels: ModelOption[] = [
    { id: 'gpt-4.1-nano', name: 'gpt-4.1-nano', inputPrice: '0.10', outputPrice: '0.40', badge: 'Cheapest', badgeColor: 'green' },
    { id: 'gpt-4.1-mini', name: 'gpt-4.1-mini', inputPrice: '0.40', outputPrice: '1.60', badge: '⚡ Fast', badgeColor: 'blue' },
    { id: 'gpt-4o-mini', name: 'gpt-4o-mini', inputPrice: '0.15', outputPrice: '0.60', badge: '⚡ Fast', badgeColor: 'blue' },
    { id: 'gpt-4.1', name: 'gpt-4.1', inputPrice: '2.00', outputPrice: '8.00', badge: 'Precise', badgeColor: 'purple' },
    { id: 'gpt-4o', name: 'gpt-4o', inputPrice: '2.50', outputPrice: '10.00', badge: 'Precise', badgeColor: 'purple' },
];

const defaultOpenrouterModels: ModelOption[] = [
    { id: 'anthropic/claude-sonnet-4', name: 'claude-sonnet-4', inputPrice: '3.00', outputPrice: '15.00', badge: 'Precise', badgeColor: 'purple' },
    { id: 'anthropic/claude-haiku-4', name: 'claude-haiku-4', inputPrice: '0.80', outputPrice: '4.00', badge: '⚡ Fast', badgeColor: 'blue' },
    { id: 'mistralai/mistral-small-3.2-24b-instruct', name: 'mistral-small-3.2', inputPrice: '0.10', outputPrice: '0.30', badge: 'Cheapest', badgeColor: 'green' },
    { id: 'deepseek/deepseek-chat-v3-0324', name: 'deepseek-v3', inputPrice: '0.30', outputPrice: '0.88', badge: '⚡ Fast', badgeColor: 'blue' },
];

const openrouterModels = ref<ModelOption[]>([...defaultOpenrouterModels]);
const fetchingModels = ref(false);
const fetchError = ref('');

async function fetchOpenRouterModels() {
    fetchingModels.value = true;
    fetchError.value = '';

    try {
        const response = await fetch('/settings/openrouter-models', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content || '',
            },
        });

        const data = await response.json();

        if (!response.ok) {
            fetchError.value = data.error || 'Failed to fetch models';
            return;
        }

        if (data.models && data.models.length > 0) {
            openrouterModels.value = data.models;
        }
    } catch {
        fetchError.value = 'Network error. Check your connection.';
    } finally {
        fetchingModels.value = false;
    }
}

// --- Summary model options ---
const summaryModelOptions = [
    { label: 'Gemini (Google)', value: 'gemini' },
    { label: 'OpenAI (GPT)', value: 'openai' },
    { label: 'OpenRouter', value: 'openrouter' },
];

// All model names mapped by provider for the summary model name selector
const summaryModelNames: Record<string, ModelOption[]> = {
    gemini: geminiModels,
    openai: openaiModels,
    openrouter: defaultOpenrouterModels,
};

const availableSummaryModelNames = computed(() => summaryModelNames[form.ai_summary_model] || geminiModels);

// --- Negatives summary state ---
const summaryText = ref(props.settings.negatives_summary || '');
const summaryMeta = ref<SummaryMeta | null>(parseMeta(props.settings.negatives_summary_meta));
const summaryStale = ref(props.settings.negatives_summary_stale);
const regenerating = ref(false);
const regenerateError = ref('');

function parseMeta(raw: SummaryMeta | string | null): SummaryMeta | null {
    if (!raw) return null;
    if (typeof raw === 'object') return raw;
    try {
        return JSON.parse(raw);
    } catch {
        return null;
    }
}

function formatBytes(bytes: number): string {
    if (bytes < 1024) return `${bytes} B`;
    return `${(bytes / 1024).toFixed(1)} KB`;
}

function formatDate(iso: string): string {
    try {
        return new Date(iso).toLocaleString('pt-BR');
    } catch {
        return iso;
    }
}

async function regenerateSummary() {
    regenerating.value = true;
    regenerateError.value = '';

    try {
        const response = await fetch('/settings/regenerate-negatives-summary', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content || '',
            },
        });

        const data = await response.json();

        if (!response.ok) {
            regenerateError.value = data.error || 'Failed to regenerate summary';
            return;
        }

        summaryText.value = data.summary;
        summaryMeta.value = data.meta;
        summaryStale.value = false;
    } catch {
        regenerateError.value = 'Network error. Check your connection.';
    } finally {
        regenerating.value = false;
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Global settings" />

        <SettingsLayout>
            <form @submit.prevent="submit" class="space-y-12">
                <!-- Match Type Defaults -->
                <div class="space-y-6">
                    <HeadingSmall title="Match type defaults" description="Default match types when adding keywords" />

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="default_keyword_match_type">Positive keyword match type</Label>
                            <Select v-model="form.default_keyword_match_type">
                                <SelectTrigger id="default_keyword_match_type">
                                    <SelectValue placeholder="Select match type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="exact">Exact</SelectItem>
                                    <SelectItem value="phrase">Phrase</SelectItem>
                                    <SelectItem value="broad">Broad</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.default_keyword_match_type" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="default_negative_keyword_match_type">Negative keyword match type</Label>
                            <Select v-model="form.default_negative_keyword_match_type">
                                <SelectTrigger id="default_negative_keyword_match_type">
                                    <SelectValue placeholder="Select match type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="exact">Exact</SelectItem>
                                    <SelectItem value="phrase">Phrase</SelectItem>
                                    <SelectItem value="broad">Broad</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.default_negative_keyword_match_type" />
                        </div>
                    </div>
                </div>

                <!-- AI Configuration -->
                <div class="space-y-6">
                    <HeadingSmall title="AI configuration" description="API keys, models, and provider settings" />

                    <div class="grid gap-2">
                        <Label for="ai_default_model">Default AI model</Label>
                        <Select v-model="form.ai_default_model">
                            <SelectTrigger id="ai_default_model" class="sm:w-1/2">
                                <SelectValue placeholder="Select model" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="gemini">Gemini (Google)</SelectItem>
                                <SelectItem value="openai">OpenAI (GPT)</SelectItem>
                                <SelectItem value="openrouter">OpenRouter</SelectItem>
                            </SelectContent>
                        </Select>
                        <p class="text-sm text-muted-foreground">Used when no model is specified for analysis.</p>
                        <InputError :message="form.errors.ai_default_model" />
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="ai_api_timeout">API timeout (seconds)</Label>
                            <Input id="ai_api_timeout" type="number" v-model="form.ai_api_timeout" min="10" max="300" />
                            <p class="text-sm text-muted-foreground">Time before AI API calls are aborted (10-300).</p>
                            <InputError :message="form.errors.ai_api_timeout" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="ai_gemini_max_output_tokens">Max output tokens</Label>
                            <Input id="ai_gemini_max_output_tokens" type="number" v-model="form.ai_gemini_max_output_tokens" min="1024" max="65536" />
                            <p class="text-sm text-muted-foreground">Maximum tokens for AI response (1024-65536).</p>
                            <InputError :message="form.errors.ai_gemini_max_output_tokens" />
                        </div>
                    </div>

                    <!-- Gemini -->
                    <div class="space-y-4 rounded-lg border p-4">
                        <h4 class="text-sm font-medium">Google Gemini</h4>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="ai_gemini_api_key">API key</Label>
                                <Input
                                    id="ai_gemini_api_key"
                                    type="password"
                                    v-model="form.ai_gemini_api_key"
                                    :placeholder="settings.has_gemini_key ? '••••••••••••••••' : 'Paste your API key'"
                                />
                                <p class="text-sm text-muted-foreground">Leave empty to keep current key.</p>
                                <InputError :message="form.errors.ai_gemini_api_key" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="ai_gemini_model">Model</Label>
                                <ModelSelect id="ai_gemini_model" v-model="form.ai_gemini_model" :models="geminiModels" />
                                <InputError :message="form.errors.ai_gemini_model" />
                            </div>
                        </div>
                    </div>

                    <!-- OpenAI -->
                    <div class="space-y-4 rounded-lg border p-4">
                        <h4 class="text-sm font-medium">OpenAI</h4>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="ai_openai_api_key">API key</Label>
                                <Input
                                    id="ai_openai_api_key"
                                    type="password"
                                    v-model="form.ai_openai_api_key"
                                    :placeholder="settings.has_openai_key ? '••••••••••••••••' : 'Paste your API key'"
                                />
                                <p class="text-sm text-muted-foreground">Leave empty to keep current key.</p>
                                <InputError :message="form.errors.ai_openai_api_key" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="ai_openai_model">Model</Label>
                                <ModelSelect id="ai_openai_model" v-model="form.ai_openai_model" :models="openaiModels" />
                                <InputError :message="form.errors.ai_openai_model" />
                            </div>
                        </div>
                    </div>

                    <!-- OpenRouter -->
                    <div class="space-y-4 rounded-lg border p-4">
                        <h4 class="text-sm font-medium">OpenRouter</h4>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="ai_openrouter_api_key">API key</Label>
                                <Input
                                    id="ai_openrouter_api_key"
                                    type="password"
                                    v-model="form.ai_openrouter_api_key"
                                    :placeholder="settings.has_openrouter_key ? '••••••••••••••••' : 'Paste your API key'"
                                />
                                <p class="text-sm text-muted-foreground">Leave empty to keep current key.</p>
                                <InputError :message="form.errors.ai_openrouter_api_key" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="ai_openrouter_model">Model</Label>
                                <div class="flex items-start gap-2">
                                    <div class="flex-1">
                                        <ModelSelect id="ai_openrouter_model" v-model="form.ai_openrouter_model" :models="openrouterModels" />
                                    </div>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        class="mt-0 shrink-0"
                                        :disabled="fetchingModels"
                                        @click="fetchOpenRouterModels"
                                    >
                                        <template v-if="fetchingModels">
                                            <svg
                                                class="-ml-1 mr-1.5 h-3.5 w-3.5 animate-spin"
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                            >
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                            </svg>
                                            Loading...
                                        </template>
                                        <template v-else>Fetch models</template>
                                    </Button>
                                </div>
                                <p v-if="fetchError" class="text-sm text-red-600">{{ fetchError }}</p>
                                <InputError :message="form.errors.ai_openrouter_model" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Negative Keywords Summary -->
                <div class="space-y-6">
                    <HeadingSmall
                        title="Negative keywords summary"
                        description="AI-synthesized profile of your negative keywords, used to reduce prompt size during analysis"
                    />

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="ai_summary_model">Summary provider</Label>
                            <Select v-model="form.ai_summary_model">
                                <SelectTrigger id="ai_summary_model">
                                    <SelectValue placeholder="Select provider" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="opt in summaryModelOptions" :key="opt.value" :value="opt.value">
                                        {{ opt.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.ai_summary_model" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="ai_summary_model_name">Summary model</Label>
                            <ModelSelect id="ai_summary_model_name" v-model="form.ai_summary_model_name" :models="availableSummaryModelNames" />
                            <InputError :message="form.errors.ai_summary_model_name" />
                        </div>
                    </div>

                    <!-- Summary metadata bar -->
                    <div v-if="summaryMeta" class="flex flex-wrap items-center gap-3 rounded-lg border bg-muted/50 px-4 py-3 text-sm">
                        <span class="text-muted-foreground">Generated:</span>
                        <span class="font-medium">{{ formatDate(summaryMeta.generated_at) }}</span>
                        <span class="text-muted-foreground">|</span>
                        <span class="text-muted-foreground">Keywords:</span>
                        <span class="font-medium">{{ summaryMeta.keyword_count }}</span>
                        <span class="text-muted-foreground">|</span>
                        <span class="text-muted-foreground">Size:</span>
                        <span class="font-medium">{{ formatBytes(summaryMeta.summary_size_bytes) }}</span>
                        <span class="text-muted-foreground">|</span>
                        <span class="text-muted-foreground">Model:</span>
                        <span class="font-medium">{{ summaryMeta.model_used }}</span>
                        <span
                            v-if="summaryStale"
                            class="ml-2 inline-flex items-center rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800"
                        >
                            Stale
                        </span>
                        <span
                            v-else
                            class="ml-2 inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800"
                        >
                            Current
                        </span>
                    </div>
                    <div v-else class="rounded-lg border border-dashed bg-muted/30 px-4 py-3 text-sm text-muted-foreground">
                        No summary generated yet. Click "Regenerate summary" to create one.
                    </div>

                    <!-- Summary preview (read-only) -->
                    <div v-if="summaryText" class="grid gap-2">
                        <Label>Current summary</Label>
                        <Textarea
                            :model-value="summaryText"
                            rows="10"
                            readonly
                            class="bg-muted/30 font-mono text-xs"
                        />
                    </div>

                    <!-- Regenerate button -->
                    <div class="flex items-center gap-3">
                        <Button
                            type="button"
                            variant="outline"
                            :disabled="regenerating"
                            @click="regenerateSummary"
                        >
                            <template v-if="regenerating">
                                <svg
                                    class="-ml-1 mr-1.5 h-3.5 w-3.5 animate-spin"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                                Generating...
                            </template>
                            <template v-else>Regenerate summary</template>
                        </Button>
                        <p v-if="regenerateError" class="text-sm text-red-600">{{ regenerateError }}</p>
                    </div>
                </div>

                <!-- Custom AI Instructions -->
                <div class="space-y-6">
                    <HeadingSmall title="Custom AI instructions" description="Additional instructions appended to AI prompts" />

                    <div class="grid gap-2">
                        <Label for="ai_global_custom_instructions">Global custom instructions</Label>
                        <Textarea
                            id="ai_global_custom_instructions"
                            v-model="form.ai_global_custom_instructions"
                            rows="4"
                            placeholder="Instructions applied to all AI models..."
                        />
                        <p class="text-sm text-muted-foreground">Applied to every AI analysis regardless of provider.</p>
                        <InputError :message="form.errors.ai_global_custom_instructions" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="ai_gemini_custom_instructions">Gemini custom instructions</Label>
                        <Textarea
                            id="ai_gemini_custom_instructions"
                            v-model="form.ai_gemini_custom_instructions"
                            rows="4"
                            placeholder="Gemini-specific instructions..."
                        />
                        <InputError :message="form.errors.ai_gemini_custom_instructions" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="ai_openai_custom_instructions">OpenAI custom instructions</Label>
                        <Textarea
                            id="ai_openai_custom_instructions"
                            v-model="form.ai_openai_custom_instructions"
                            rows="4"
                            placeholder="OpenAI-specific instructions..."
                        />
                        <InputError :message="form.errors.ai_openai_custom_instructions" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="ai_openrouter_custom_instructions">OpenRouter custom instructions</Label>
                        <Textarea
                            id="ai_openrouter_custom_instructions"
                            v-model="form.ai_openrouter_custom_instructions"
                            rows="4"
                            placeholder="OpenRouter-specific instructions..."
                        />
                        <InputError :message="form.errors.ai_openrouter_custom_instructions" />
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex items-center gap-4">
                    <Button :disabled="form.processing">Save settings</Button>

                    <Transition
                        enter-active-class="transition ease-in-out"
                        enter-from-class="opacity-0"
                        leave-active-class="transition ease-in-out"
                        leave-to-class="opacity-0"
                    >
                        <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
                    </Transition>
                </div>
            </form>
        </SettingsLayout>
    </AppLayout>
</template>
