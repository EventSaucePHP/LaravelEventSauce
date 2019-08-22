<?php

declare(strict_types=1);

namespace Tests;

use EventSauce\LaravelEventSauce\HandleConsumer;
use EventSauce\LaravelEventSauce\LaravelMessageDispatcher;
use Illuminate\Support\Facades\Bus;
use Tests\Fixtures\SendConfirmationNotification;

class LaravelMessageDispatcherTest extends TestCase
{
    /** @test */
    public function it_can_dispatch_messages()
    {
        $message = $this->getUserWasRegisteredMessage();

        Bus::fake();

        $this->dispatcher()->dispatch($message);

        Bus::assertDispatched(HandleConsumer::class);
    }

    private function dispatcher(): LaravelMessageDispatcher
    {
        return new LaravelMessageDispatcher(
            SendConfirmationNotification::class
        );
    }
}
