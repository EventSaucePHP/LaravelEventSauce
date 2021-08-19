<?php

declare(strict_types=1);

namespace EventSauce\LaravelEventSauce;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\AggregateRootRepository as EventSauceAggregateRootRepository;
use EventSauce\EventSourcing\ConstructingAggregateRootRepository;
use EventSauce\EventSourcing\MessageDecorator;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\MessageDispatcherChain;
use LogicException;

abstract class AggregateRootRepository implements EventSauceAggregateRootRepository
{
    private LaravelMessageRepository $messageRepository;

    protected string $aggregateRoot = '';

    protected array $consumers = [];

    protected string $connection = '';

    protected string $table = '';

    protected string $queue = '';

    protected static string $inputFile = '';

    protected static string $outputFile = '';

    public function __construct(LaravelMessageRepository $messageRepository)
    {
        if (! is_a($this->aggregateRoot, AggregateRoot::class, true)) {
            throw new LogicException('You have to set an aggregate root before the repository can be initialized.');
        }

        $this->messageRepository = $messageRepository;

        if ($this->connection) {
            $this->messageRepository->setConnection($this->connection);
        }

        if ($this->table) {
            $this->messageRepository->setTable($this->table);
        }
    }

    public function retrieve(AggregateRootId $aggregateRootId): object
    {
        return $this->repository()->retrieve($aggregateRootId);
    }

    public function persist(object $aggregateRoot)
    {
        $this->repository()->persist($aggregateRoot);
    }

    public function persistEvents(AggregateRootId $aggregateRootId, int $aggregateRootVersion, object ...$events)
    {
        $this->repository()->persistEvents($aggregateRootId, $aggregateRootVersion, ...$events);
    }

    private function repository(): EventSauceAggregateRootRepository
    {
        return new ConstructingAggregateRootRepository(
            $this->aggregateRoot,
            $this->messageRepository,
            new MessageDispatcherChain(
                $this->buildLaravelMessageDispatcher(),
                new EventMessageDispatcher(),
            ),
            resolve(MessageDecorator::class)
        );
    }

    public static function inputFile(): string
    {
        return static::$inputFile;
    }

    public static function outputFile(): string
    {
        return static::$outputFile;
    }

    private function buildLaravelMessageDispatcher(): MessageDispatcher
    {
        $dispatcher = new LaravelMessageDispatcher(
            ...$this->consumers,
        );

        if ($this->queue) {
            $dispatcher->onQueue($this->queue);
        }

        return $dispatcher;
    }
}
