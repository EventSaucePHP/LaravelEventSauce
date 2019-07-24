<?php

namespace Tests;

use EventSauce\LaravelEventSauce\EventSauceJob;
use EventSauce\LaravelEventSauce\LaravelMessageDispatcher;
use Illuminate\Support\Facades\Bus;

class LaravelMessageDispatcherTest extends TestCase
{
    /** @test */
    public function it_can_dispatch_messages()
    {
        $message = $this->buildUserWasRegisteredMessage();

        Bus::fake();

        $this->dispatcher()->dispatch($message);

        Bus::assertDispatched(EventSauceJob::class);
    }

    private function dispatcher(): LaravelMessageDispatcher
    {
        return $this->app->make(LaravelMessageDispatcher::class);
    }
}
