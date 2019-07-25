<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use EventSauce\EventSourcing\Consumer;
use EventSauce\EventSourcing\Message;

final class SendConfirmationEmail implements Consumer
{
    public function handle(Message $message)
    {
        // Dummy handler...
    }
}
