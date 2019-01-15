<?php

namespace EventSauce\LaravelEventSauce;

use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class EventSauceJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    /**
     * @var \EventSauce\EventSourcing\Message[]
     */
    private $messages = [];

    public function __construct(Message ...$messages)
    {
        $this->messages = $messages;
    }

    public function handle(MessageDispatcher $dispatcher): void
    {
        $dispatcher->dispatch(...$this->messages);
    }
}
