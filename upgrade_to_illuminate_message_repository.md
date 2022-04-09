# Upgrade to EventSauce's IlluminateMessageRepository

The upgrade from version 0.5.0 to 1.0.0 replaced the message repository from the package by the  IlluminateMessageRepository provided by EventSauce. (https://eventsauce.io/docs/message-storage/illuminate/)

This update requires a small migration to add the `version` column to your messages table(s). 

```php
Schema::table('{your_table_name}', function (Blueprint $table){
    $table->unsignedInteger('version')->nullable();
});

DB::update("update {your_table_name} set version = JSON_EXTRACT(payload, '$.headers.__aggregate_root_version')");

Schema::table('{your_table_name}', function (Blueprint $table){
    $table->unsignedInteger('version')->nullable(false)->change();
}); 
```

> Note, the example requires `doctrine/dbal`, in order to make `version` not nullable. https://laravel.com/docs/8.x/migrations#prerequisites
