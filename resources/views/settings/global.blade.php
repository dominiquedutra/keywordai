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
                    <h2 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-300">Configurações de Inteligência Artificial</h2>
                    
                    <div class="bg-blue-50 dark:bg-blue-900 border-l-4 border-blue-400 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700 dark:text-blue-200">
                                    As chaves de API e versões dos modelos de IA são configuradas no arquivo <code>.env</code>.
                                </p>
                            </div>
                        </div>
                    </div>

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

                        <!-- Instruções Customizadas para Perplexity -->
                        <div>
                            <label for="ai_perplexity_custom_instructions" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Instruções Customizadas para Perplexity
                            </label>
                            <textarea
                                id="ai_perplexity_custom_instructions"
                                name="ai_perplexity_custom_instructions"
                                rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >{{ Setting::getValue('ai_perplexity_custom_instructions') }}</textarea>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Instruções customizadas específicas para o modelo Perplexity.
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
