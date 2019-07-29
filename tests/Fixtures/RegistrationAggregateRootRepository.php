<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use EventSauce\LaravelEventSauce\AggregateRootRepository;

final class RegistrationAggregateRootRepository extends AggregateRootRepository
{
    /** @var string */
    protected $aggregateRoot = RegistrationAggregateRoot::class;

    /** @var array */
    protected $syncConsumers = [
        UpdateUsersTable::class,
    ];

    /** @var array */
    protected $asyncConsumers = [
        SendConfirmationNotification::class,
    ];

    /** @var string */
    protected static $inputFile = __DIR__ . '/commands_and_events.yml';

    /** @var string */
    protected static $outputFile = __DIR__ . '/commands_and_events.php';
}
