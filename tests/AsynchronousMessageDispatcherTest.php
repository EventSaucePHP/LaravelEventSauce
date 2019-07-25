<?php

namespace Tests;

use EventSauce\LaravelEventSauce\HandleAsyncConsumer;
use EventSauce\LaravelEventSauce\AsynchronousMessageDispatcher;
use Illuminate\Support\Facades\Bus;
use Tests\Fixtures\SendConfirmationEmail;

class AsynchronousMessageDispatcherTest extends TestCase
{
    /** @test */
    public function it_can_dispatch_messages()
    {
        $message = $this->buildUserWasRegisteredMessage();

        Bus::fake();

        $this->dispatcher()->dispatch($message);

        Bus::assertDispatched(HandleAsyncConsumer::class);
    }

    private function dispatcher(): AsynchronousMessageDispatcher
    {
        return new AsynchronousMessageDispatcher(
            SendConfirmationEmail::class
        );
    }
}
