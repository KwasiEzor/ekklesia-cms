<?php

return [
    'default' => env('AI_DEFAULT_PROVIDER', 'claude'),

    'providers' => [
        'claude' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-6'),
            'max_tokens' => (int) env('AI_MAX_TOKENS', 2048),
        ],
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4o'),
            'max_tokens' => (int) env('AI_MAX_TOKENS', 2048),
        ],
        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
            'max_tokens' => (int) env('AI_MAX_TOKENS', 2048),
        ],
    ],
];
