<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Model
    |--------------------------------------------------------------------------
    |
    | This value determines which AI model will be used by default when no
    | specific model is requested. The available options are 'gemini',
    | 'openai', and 'openrouter'.
    |
    */
    'default_model' => env('AI_DEFAULT_MODEL', 'gemini'),

    /*
    |--------------------------------------------------------------------------
    | AI Models Configuration
    |--------------------------------------------------------------------------
    |
    | This array contains the configuration for each supported AI model.
    | Each model has its own API key and endpoint configuration.
    |
    */
    'models' => [
        'gemini' => [
            'api_key' => env('AI_GEMINI_API_KEY'),
            'model_name' => env('AI_GEMINI_MODEL', 'gemini-2.5-flash'),
            'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent',
        ],
        'openai' => [
            'api_key' => env('AI_OPENAI_API_KEY'),
            'model_name' => env('AI_OPENAI_MODEL', 'gpt-4o'),
            'endpoint' => 'https://api.openai.com/v1/chat/completions',
        ],
        'openrouter' => [
            'api_key' => env('AI_OPENROUTER_API_KEY'),
            'model_name' => env('AI_OPENROUTER_MODEL', 'google/gemini-2.0-flash-001'),
            'endpoint' => 'https://openrouter.ai/api/v1/chat/completions',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Instructions
    |--------------------------------------------------------------------------
    |
    | These are the custom instructions that will be sent to the AI models.
    | The global instructions apply to all models, while the model-specific
    | instructions are only applied to their respective models.
    |
    | Note: These instructions are stored in the database, not in the .env file.
    | They can be configured in the Settings page.
    |
    */
];
