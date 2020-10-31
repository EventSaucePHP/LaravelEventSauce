<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use EventSauce\LaravelEventSauce\AggregateRootRepository;

final class RegistrationAggregateRootRepository extends AggregateRootRepository
{
    protected string $aggregateRoot = RegistrationAggregateRoot::class;

    protected array $consumers = [
        UpdateUsersTable::class,
        SendConfirmationNotification::class,
    ];

    protected static string $inputFile = __DIR__.'/commands_and_events.yml';

    protected static string $outputFile = __DIR__.'/commands_and_events.php';
}
