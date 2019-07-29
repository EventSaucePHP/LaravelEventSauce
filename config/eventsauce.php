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
     * We can generate commands and events for you starting from a Yaml file.
     * Here you can specify the repositories for which we should generate them.
     *
     * More info on code generation here:
     * https://eventsauce.io/docs/getting-started/create-events-and-commands
     */
    'code_generation' => [
        // MyAggregateRootRepository::class,
    ],

];
