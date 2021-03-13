<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use EventSauce\LaravelEventSauce\Consumer;
use Illuminate\Contracts\Queue\ShouldQueue;

final class QueueableConsumer extends Consumer implements ShouldQueue
{
    protected function handleUserWasRegistered(UserWasRegistered $event): void
    {
    }
}
