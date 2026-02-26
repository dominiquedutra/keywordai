<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';

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
    has_gemini_key: boolean;
    has_openai_key: boolean;
    has_openrouter_key: boolean;
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
});

const submit = () => {
    form.patch(route('settings.global.update'), {
        preserveScroll: true,
    });
};
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

                    <!-- Gemini -->
                    <div class="rounded-lg border p-4 space-y-4">
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
                                <Input id="ai_gemini_model" v-model="form.ai_gemini_model" />
                                <p class="text-sm text-muted-foreground">
                                    e.g. <code class="text-xs">gemini-2.0-flash</code>, <code class="text-xs">gemini-2.5-pro</code>
                                </p>
                                <InputError :message="form.errors.ai_gemini_model" />
                            </div>
                        </div>
                    </div>

                    <!-- OpenAI -->
                    <div class="rounded-lg border p-4 space-y-4">
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
                                <Input id="ai_openai_model" v-model="form.ai_openai_model" />
                                <p class="text-sm text-muted-foreground">
                                    e.g. <code class="text-xs">gpt-4o-mini</code>, <code class="text-xs">gpt-4o</code>
                                </p>
                                <InputError :message="form.errors.ai_openai_model" />
                            </div>
                        </div>
                    </div>

                    <!-- OpenRouter -->
                    <div class="rounded-lg border p-4 space-y-4">
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
                                <Input id="ai_openrouter_model" v-model="form.ai_openrouter_model" />
                                <p class="text-sm text-muted-foreground">
                                    e.g. <code class="text-xs">google/gemini-2.0-flash-001</code>, <code class="text-xs">anthropic/claude-sonnet-4</code>
                                </p>
                                <InputError :message="form.errors.ai_openrouter_model" />
                            </div>
                        </div>
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
