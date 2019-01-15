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

            /*
             * We can generate  types, commands and events for you starting from a yaml file.
             * Here you can specify the input and the output.
             *
             * More info on code generation here: https://eventsauce.io/docs/getting-started/create-events-and-commands
             */
            'code_generation' => [
                'input_yaml_file' => null,
                'output_file' => null,
            ],
        ],
    ],
];
