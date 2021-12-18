<?php

declare(strict_types=1);

namespace EventSauce\LaravelEventSauce;

use function array_unshift;
use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\AggregateRootRepository as EventSauceAggregateRootRepository;
use EventSauce\EventSourcing\ConstructingAggregateRootRepository;
use EventSauce\EventSourcing\DefaultHeadersDecorator;
use EventSauce\EventSourcing\MessageDecoratorChain;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\MessageDispatcherChain;
use function in_array;
use LogicException;
use function resolve;

abstract class AggregateRootRepository implements EventSauceAggregateRootRepository
{
    private LaravelMessageRepository $messageRepository;

    protected string $aggregateRoot = '';

    protected array $consumers = [];

    protected array $decorators = [];

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

    public function persist(object $aggregateRoot): void
    {
        $this->repository()->persist($aggregateRoot);
    }

    public function persistEvents(AggregateRootId $aggregateRootId, int $aggregateRootVersion, object ...$events): void
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
            new MessageDecoratorChain(...$this->buildMessageDecorators()),
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

    private function buildMessageDecorators(): array
    {
        if (! in_array(DefaultHeadersDecorator::class, $this->decorators)) {
            array_unshift($this->decorators, DefaultHeadersDecorator::class);
        }

        $decorators = [];

        foreach ($this->decorators as $decorator) {
            $decorators[] = resolve($decorator);
        }

        return $decorators;
    }
}
