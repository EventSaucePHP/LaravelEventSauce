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
            'definition' => __DIR__.'/../app/CatShelter/Intake/commands-and-events.yml',
            'output' => __DIR__.'/../app/CatShelter/Intake/commands-and-events.php',
        ],
    ],
];
