<?php

namespace EventSauce\LaravelEventSauce;

use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDispatcher;

final class LaravelMessageDispatcher implements MessageDispatcher
{
    public function dispatch(Message ... $messages)
    {
        foreach ($messages as $message) {
            EventSauceJob::dispatch($message);
        }
    }
}
