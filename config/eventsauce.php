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
     * Here you specify all of your aggregate root repositories.
     * We'll use this info to generate commands and events.
     *
     * More info on code generation here:
     * https://eventsauce.io/docs/event-sourcing/create-events-and-commands
     */

    'repositories' => [
        // App\Domain\MyAggregateRoot\MyAggregateRootRepository::class,
    ],

];
