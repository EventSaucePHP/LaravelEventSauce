<?php

namespace Tests\Fixtures;

use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDecorator;
use LogicException;

class ExceptionThrowingMessageDecorator implements MessageDecorator
{
    public function decorate(Message $message): Message
    {
        throw new LogicException("A message decorator was triggered");
    }
}
