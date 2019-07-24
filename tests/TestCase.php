<?php

declare(strict_types=1);

namespace Tests;

use EventSauce\EventSourcing\DefaultHeadersDecorator;
use EventSauce\EventSourcing\Message;
use EventSauce\LaravelEventSauce\EventSauceServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Tests\Fixtures\UserWasRegistered;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [EventSauceServiceProvider::class];
    }

    protected function buildUserWasRegisteredMessage(): Message
    {
        $decorator = new DefaultHeadersDecorator();
        $message = new Message(
            new UserWasRegistered('Dries Vints', 'dries.vints@gmail.com')
        );

        return $decorator->decorate($message);
    }
}
