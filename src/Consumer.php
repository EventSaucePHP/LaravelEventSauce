<?php

namespace EventSauce\LaravelEventSauce;

use EventSauce\EventSourcing\Consumer as ConsumerContract;
use EventSauce\EventSourcing\Message;

abstract class Consumer implements ConsumerContract
{
    public function handle(Message $message): void
    {
        $event = $message->event();
        $parts = explode('\\', get_class($event));
        $method = 'handle' . end($parts);

        if (method_exists($this, $method)) {
            $this->{$method}($event, $message);
        }
    }
}
