<?php

declare(strict_types=1);

namespace EventSauce\LaravelEventSauce;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\AggregateRootRepository as EventSauceAggregateRootRepository;
use EventSauce\EventSourcing\ConstructingAggregateRootRepository;
use EventSauce\EventSourcing\MessageDispatcherChain;
use EventSauce\EventSourcing\SynchronousMessageDispatcher;
use Illuminate\Contracts\Container\Container;
use LogicException;

abstract class AggregateRootRepository implements EventSauceAggregateRootRepository
{
    /** @var LaravelMessageRepository */
    private $messageRepository;

    /** @var Container */
    private $container;

    /** @var string */
    protected $aggregateRoot;

    /** @var array */
    protected $syncConsumers = [];

    /** @var array */
    protected $asyncConsumers = [];

    /** @var string */
    protected $connection;

    /** @var string */
    protected $table;

    public function __construct(LaravelMessageRepository $messageRepository, Container $container)
    {
        if ($this->aggregateRoot === null) {
            throw new LogicException("You have to set an aggregate root before the repository can be initialized.");
        }

        $this->messageRepository = $messageRepository;
        $this->container = $container;

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
                new SynchronousMessageDispatcher(
                    ...$this->resolveConsumers($this->syncConsumers)
                ),
                new AsynchronousMessageDispatcher(
                    ...$this->asyncConsumers
                ),
                new EventMessageDispatcher()
            )
        );
    }

    private function resolveConsumers(array $consumers): array
    {
        return array_map(function (string $consumer) {
            return $this->container->make($consumer);
        }, $consumers);
    }
}
