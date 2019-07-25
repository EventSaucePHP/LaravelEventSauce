<?php

namespace Tests;

use EventSauce\LaravelEventSauce\EventMessageDispatcher;
use Illuminate\Support\Facades\Event;
use Tests\Fixtures\UserWasRegistered;

class EventMessageDispatcherTest extends TestCase
{
    /** @test */
    public function it_can_dispatch_messages()
    {
        $message = $this->buildUserWasRegisteredMessage();

        Event::fake();

        $this->dispatcher()->dispatch($message);

        Event::assertDispatched(UserWasRegistered::class);
    }

    private function dispatcher(): EventMessageDispatcher
    {
        return new EventMessageDispatcher();
    }
}
