<?php

return [

    /*
     * The default database connection name, used to store messages.
     * When null is provided it'll use the default application connection.
     */
    'connection' => env('EVENTSAUCE_CONNECTION'),

    /*
     * The default database table name, used to store messages.
     */
    'table' => env('EVENTSAUCE_TABLE', 'domain_messages'),

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

];
