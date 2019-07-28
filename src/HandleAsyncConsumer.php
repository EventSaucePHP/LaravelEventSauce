<?php

declare(strict_types=1);

namespace EventSauce\LaravelEventSauce;

use EventSauce\EventSourcing\Consumer;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\SynchronousMessageDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class HandleAsyncConsumer implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    /** @var string */
    private $consumer;

    /** @var Message[] */
    private $messages = [];

    public function __construct(string $consumer, Message ...$messages)
    {
        $this->consumer = $consumer;
        $this->messages = $messages;
    }

    public function handle(Container $container): void
    {
        $consumer = $this->resolveConsumer($container);

        (new SynchronousMessageDispatcher($consumer))
            ->dispatch(...$this->messages);
    }

    private function resolveConsumer(Container $container): Consumer
    {
        return $container->make($this->consumer);
    }
}
