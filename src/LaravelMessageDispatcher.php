<?php

declare(strict_types=1);

namespace EventSauce\LaravelEventSauce;

use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDispatcher;

final class LaravelMessageDispatcher implements MessageDispatcher
{
    public function dispatch(Message ... $messages)
    {
        foreach ($messages as $message) {
            dispatch(new EventSauceJob($message));
        }
    }
}
