<?php

namespace EventSauce\LaravelEventSauce;

use EventSauce\EventSourcing\Header;
use EventSauce\MessageRepository\TableSchema\TableSchema;

class LaravelEventSauceTableSchema implements TableSchema
{

    public function eventIdColumn(): string
    {
        return 'event_id';
    }

    public function aggregateRootIdColumn(): string
    {
        return 'event_stream';
    }

    public function versionColumn(): string
    {
        return 'version';
    }

    public function payloadColumn(): string
    {
        return 'payload';
    }

    public function additionalColumns(): array
    {
        return [
            'recorded_at' => Header::TIME_OF_RECORDING,
            'event_type' => Header::EVENT_TYPE
        ];
    }
}
