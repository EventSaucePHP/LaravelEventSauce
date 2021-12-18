<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use EventSauce\EventSourcing\Consumer;
use EventSauce\EventSourcing\Message;

final class UpdateUsersTable implements Consumer
{
    public function handle(Message $message): void
    {
        $event = $message->event();

        if ($event instanceof UserWasRegistered) {
            User::create([
                'name' => $event->name(),
                'email' => $event->email(),
            ]);
        }
    }
}
