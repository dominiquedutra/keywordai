@extends('layouts.app')

@section('title', 'Configurações Globais')

@section('content')
<?php
use App\Models\Setting;
?>
        <h1 class="text-3xl font-bold mb-6 text-gray-800 dark:text-gray-200">Configurações Globais</h1>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
            <form method="POST" action="{{ route('settings.global.update') }}">
                @csrf
                
                <div class="mb-4">
                    <h2 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-300">Configurações de Tipo de Correspondência</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Tipo de Correspondência Padrão para Palavras-Chave Positivas -->
                        <div>
                            <label for="default_keyword_match_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Tipo de Correspondência Padrão para Palavras-Chave Positivas
                            </label>
                            <select 
                                id="default_keyword_match_type" 
                                name="default_keyword_match_type" 
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="exact" {{ Setting::getValue('default_keyword_match_type') == 'exact' ? 'selected' : '' }}>Exata (Exact)</option>
                                <option value="phrase" {{ Setting::getValue('default_keyword_match_type') == 'phrase' ? 'selected' : '' }}>Frase (Phrase)</option>
                                <option value="broad" {{ Setting::getValue('default_keyword_match_type') == 'broad' ? 'selected' : '' }}>Ampla (Broad)</option>
                            </select>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Define o tipo de correspondência padrão ao adicionar palavras-chave positivas.
                            </p>
                        </div>
                        
                        <!-- Tipo de Correspondência Padrão para Palavras-Chave Negativas -->
                        <div>
                            <label for="default_negative_keyword_match_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Tipo de Correspondência Padrão para Palavras-Chave Negativas
                            </label>
                            <select 
                                id="default_negative_keyword_match_type" 
                                name="default_negative_keyword_match_type" 
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="exact" {{ Setting::getValue('default_negative_keyword_match_type') == 'exact' ? 'selected' : '' }}>Exata (Exact)</option>
                                <option value="phrase" {{ Setting::getValue('default_negative_keyword_match_type') == 'phrase' ? 'selected' : '' }}>Frase (Phrase)</option>
                                <option value="broad" {{ Setting::getValue('default_negative_keyword_match_type') == 'broad' ? 'selected' : '' }}>Ampla (Broad)</option>
                            </select>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Define o tipo de correspondência padrão ao adicionar palavras-chave negativas.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mb-4 mt-8 border-t pt-6">
                    <h2 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-300">Configuração de IA — Chaves de API e Modelos</h2>

                    <!-- Modelo Padrão -->
                    <div class="mb-6">
                        <label for="ai_default_model" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Modelo de IA Padrão
                        </label>
                        <select
                            id="ai_default_model"
                            name="ai_default_model"
                            class="mt-1 block w-full md:w-1/3 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                            <option value="gemini" {{ Setting::getValue('ai_default_model', 'gemini') == 'gemini' ? 'selected' : '' }}>Gemini (Google)</option>
                            <option value="openai" {{ Setting::getValue('ai_default_model', 'gemini') == 'openai' ? 'selected' : '' }}>OpenAI (GPT)</option>
                            <option value="openrouter" {{ Setting::getValue('ai_default_model', 'gemini') == 'openrouter' ? 'selected' : '' }}>OpenRouter</option>
                        </select>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Modelo usado quando nenhum é especificado na análise.
                        </p>
                    </div>

                    <!-- Gemini -->
                    <div class="mb-6 p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                        <h3 class="text-lg font-semibold mb-3 text-gray-700 dark:text-gray-300">Google Gemini</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="ai_gemini_api_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Chave de API
                                </label>
                                <input type="password"
                                       id="ai_gemini_api_key"
                                       name="ai_gemini_api_key"
                                       value=""
                                       placeholder="{{ !empty(Setting::getValue('ai_gemini_api_key')) ? '••••••••••••••••' : 'Cole sua API key aqui' }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Deixe vazio para manter a chave atual.{{ !empty(config('ai.models.gemini.api_key')) ? ' Fallback: .env configurado.' : '' }}
                                </p>
                            </div>
                            <div>
                                <label for="ai_gemini_model" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Modelo
                                </label>
                                <input type="text"
                                       id="ai_gemini_model"
                                       name="ai_gemini_model"
                                       value="{{ Setting::getValue('ai_gemini_model', 'gemini-2.0-flash') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Sugestões: <code>gemini-2.0-flash</code> (rápido), <code>gemini-2.5-flash-preview-04-17</code>, <code>gemini-2.5-pro</code>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- OpenAI -->
                    <div class="mb-6 p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                        <h3 class="text-lg font-semibold mb-3 text-gray-700 dark:text-gray-300">OpenAI</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="ai_openai_api_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Chave de API
                                </label>
                                <input type="password"
                                       id="ai_openai_api_key"
                                       name="ai_openai_api_key"
                                       value=""
                                       placeholder="{{ !empty(Setting::getValue('ai_openai_api_key')) ? '••••••••••••••••' : 'Cole sua API key aqui' }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Deixe vazio para manter a chave atual.{{ !empty(config('ai.models.openai.api_key')) ? ' Fallback: .env configurado.' : '' }}
                                </p>
                            </div>
                            <div>
                                <label for="ai_openai_model" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Modelo
                                </label>
                                <input type="text"
                                       id="ai_openai_model"
                                       name="ai_openai_model"
                                       value="{{ Setting::getValue('ai_openai_model', 'gpt-4o-mini') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Sugestões: <code>gpt-4o-mini</code> (mais barato), <code>gpt-4o</code> (equilibrado)
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- OpenRouter -->
                    <div class="mb-6 p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                        <h3 class="text-lg font-semibold mb-3 text-gray-700 dark:text-gray-300">OpenRouter</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="ai_openrouter_api_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Chave de API
                                </label>
                                <input type="password"
                                       id="ai_openrouter_api_key"
                                       name="ai_openrouter_api_key"
                                       value=""
                                       placeholder="{{ !empty(Setting::getValue('ai_openrouter_api_key')) ? '••••••••••••••••' : 'Cole sua API key aqui' }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Deixe vazio para manter a chave atual.{{ !empty(config('ai.models.openrouter.api_key')) ? ' Fallback: .env configurado.' : '' }}
                                </p>
                            </div>
                            <div>
                                <label for="ai_openrouter_model" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Modelo
                                </label>
                                <input type="text"
                                       id="ai_openrouter_model"
                                       name="ai_openrouter_model"
                                       value="{{ Setting::getValue('ai_openrouter_model', 'google/gemini-2.0-flash-001') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Sugestões: <code>google/gemini-2.0-flash-001</code>, <code>anthropic/claude-sonnet-4</code>, <code>meta-llama/llama-4-maverick</code>, <code>mistralai/mistral-small-3.1-24b-instruct</code>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4 mt-8 border-t pt-6">
                    <h2 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-300">Instruções Customizadas de IA</h2>

                    <div class="grid grid-cols-1 gap-6">
                        <!-- Instruções Globais Customizadas -->
                        <div>
                            <label for="ai_global_custom_instructions" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Instruções Globais Customizadas
                            </label>
                            <textarea
                                id="ai_global_custom_instructions"
                                name="ai_global_custom_instructions"
                                rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >{{ Setting::getValue('ai_global_custom_instructions') }}</textarea>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Instruções customizadas que serão aplicadas a todos os modelos de IA.
                            </p>
                        </div>

                        <!-- Instruções Customizadas para Gemini -->
                        <div>
                            <label for="ai_gemini_custom_instructions" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Instruções Customizadas para Gemini
                            </label>
                            <textarea
                                id="ai_gemini_custom_instructions"
                                name="ai_gemini_custom_instructions"
                                rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >{{ Setting::getValue('ai_gemini_custom_instructions') }}</textarea>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Instruções customizadas específicas para o modelo Gemini.
                            </p>
                        </div>

                        <!-- Instruções Customizadas para OpenAI -->
                        <div>
                            <label for="ai_openai_custom_instructions" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Instruções Customizadas para OpenAI
                            </label>
                            <textarea
                                id="ai_openai_custom_instructions"
                                name="ai_openai_custom_instructions"
                                rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >{{ Setting::getValue('ai_openai_custom_instructions') }}</textarea>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Instruções customizadas específicas para o modelo OpenAI.
                            </p>
                        </div>

                        <!-- Instruções Customizadas para OpenRouter -->
                        <div>
                            <label for="ai_openrouter_custom_instructions" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Instruções Customizadas para OpenRouter
                            </label>
                            <textarea
                                id="ai_openrouter_custom_instructions"
                                name="ai_openrouter_custom_instructions"
                                rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >{{ Setting::getValue('ai_openrouter_custom_instructions') }}</textarea>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Instruções customizadas específicas para o modelo OpenRouter.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end mt-6">
                    <button 
                        type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150"
                    >
                        Salvar Configurações
                    </button>
                </div>
            </form>
        </div>
@endsection
