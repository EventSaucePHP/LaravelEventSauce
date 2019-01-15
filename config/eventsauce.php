<?php

return [
    'aggregate_roots' => [
        'aggregate_root' => [
            'class' => null,
            'repository' => null,
            'sync_consumers' => [
                // ...
            ],
            'async_consumers' => [
                // ...
            ],
            'code_generation' => [
                'definition_file' => __DIR__.'/../app/Domain/Account/commands-and-events.yml',
                'output_to_file' => __DIR__.'/../app/Domain/Account/commands-and-events.php',
            ],
        ],
    ],
];
