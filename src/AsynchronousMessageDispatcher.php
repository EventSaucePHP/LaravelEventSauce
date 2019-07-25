<?php

declare(strict_types=1);

namespace EventSauce\LaravelEventSauce;

use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDispatcher;

final class AsynchronousMessageDispatcher implements MessageDispatcher
{
    /** @var string[] */
    private $consumers;

    public function __construct(string ...$consumers)
    {
        $this->consumers = $consumers;
    }

    public function dispatch(Message ...$messages)
    {
        foreach ($this->consumers as $consumer) {
            dispatch(new HandleAsyncConsumer($consumer, ...$messages));
        }
    }
}
