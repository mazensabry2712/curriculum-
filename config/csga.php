<?php

return [
    'ai' => [
        'provider' => env('CSGA_AI_PROVIDER', 'openai'),
        'model' => env('CSGA_AI_MODEL', 'gpt-4.1-mini'),
        'timeout' => (int) env('CSGA_AI_TIMEOUT', 120),
    ],

    'documents' => [
        'max_kb' => (int) env('CSGA_MAX_DOCUMENT_KB', 15360),
        'allowed_mimes' => ['application/pdf'],
        'private_disk' => 'local',
    ],

    'analysis' => [
        'minimum_confidence' => 0.60,
        'elective_course_weight' => 0.65,
    ],
];