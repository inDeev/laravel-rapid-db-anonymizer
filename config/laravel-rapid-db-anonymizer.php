<?php
return [
    'anonymizer' => [
        'chunk_size' => 500,
        'forbidden_environments' => [
            'production', 'prod'
        ],
        'model_dir' => 'app/Models',
        'model_namespace' => '\\App\\Models\\',
    ],
    'faker' => [
        'locale' => 'en_US'
    ]
];
