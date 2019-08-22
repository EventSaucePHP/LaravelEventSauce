<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use EventSauce\LaravelEventSauce\Consumer;

final class SendConfirmationNotification extends Consumer
{
    protected function handleUserWasRegistered(UserWasRegistered $event): void
    {
        User::where('email', $event->email())
            ->first()
            ->notify(new NewUserNotification());
    }
}
