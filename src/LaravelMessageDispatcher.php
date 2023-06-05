<?php

declare(strict_types=1);

namespace EventSauce\LaravelEventSauce;

use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;

final class LaravelMessageDispatcher implements MessageDispatcher
{
    /** @var string[] */
    private $consumers;

    private string $queue = '';

    public function __construct(string ...$consumers)
    {
        $this->consumers = $consumers;
    }

    public function dispatch(Message ...$messages): void
    {
        foreach ($this->consumers as $consumer) {
            if (is_a($consumer, ShouldQueue::class, true)) {
                $dispatch = dispatch(new HandleConsumer($consumer, ...$messages));

                if ($this->queue) {
                    $dispatch->onQueue($this->queue);
                }
            } else {
                dispatch_sync(new HandleConsumer($consumer, ...$messages));
            }
        }
    }

    public function onQueue(string $queue): self
    {
        $this->queue = $queue;

        return $this;
    }
}
