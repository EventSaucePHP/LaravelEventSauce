<?php

declare(strict_types=1);

namespace Tests;

use EventSauce\LaravelEventSauce\HandleConsumer;
use EventSauce\LaravelEventSauce\LaravelMessageDispatcher;
use Illuminate\Support\Facades\Bus;
use Tests\Fixtures\QueueableConsumer;
use Tests\Fixtures\SendConfirmationNotification;

class LaravelMessageDispatcherTest extends TestCase
{
    /** @test */
    public function it_can_dispatch_messages()
    {
        $message = $this->getUserWasRegisteredMessage();

        Bus::fake();

        $this->dispatcher(SendConfirmationNotification::class)->dispatch($message);

        Bus::assertDispatched(HandleConsumer::class);
    }

    /** @test */
    public function it_can_dispatch_messages_on_specific_queue()
    {
        $message = $this->getUserWasRegisteredMessage();

        Bus::fake();

        $this->dispatcher(QueueableConsumer::class)->setQueue('eventsource-queue')->dispatch($message);

        Bus::assertDispatched(HandleConsumer::class, function (HandleConsumer $job){
            $this->assertEquals('eventsource-queue', $job->queue);
            return true;
        });
    }

    private function dispatcher(...$consumers): LaravelMessageDispatcher
    {
        return new LaravelMessageDispatcher(...$consumers);
    }
}
