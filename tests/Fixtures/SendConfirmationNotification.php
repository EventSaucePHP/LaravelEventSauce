<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use EventSauce\EventSourcing\Consumer;
use EventSauce\EventSourcing\Message;

final class SendConfirmationNotification implements Consumer
{
    public function handle(Message $message)
    {
        $event = $message->event();

        if ($event instanceof UserWasRegistered) {
            User::where('email', $event->email())
                ->first()
                ->notify(new NewUserNotification());
        }
    }
}
