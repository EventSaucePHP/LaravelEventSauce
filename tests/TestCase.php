<?php

declare(strict_types=1);

namespace Tests;

use EventSauce\EventSourcing\DefaultHeadersDecorator;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use EventSauce\LaravelEventSauce\EventSauceServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Tests\Fixtures\RegistrationAggregateRootId;
use Tests\Fixtures\UserWasRegistered;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [EventSauceServiceProvider::class];
    }

    protected function getUserWasRegisteredMessage(RegistrationAggregateRootId $id = null): Message
    {
        $event = new UserWasRegistered('Dries Vints', 'dries.vints@gmail.com');
        $id = $id ?? RegistrationAggregateRootId::create();

        return (new DefaultHeadersDecorator())
            ->decorate(new Message($event, [Header::AGGREGATE_ROOT_ID => $id]));
    }
}
