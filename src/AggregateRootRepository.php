<?php

declare(strict_types=1);

namespace EventSauce\LaravelEventSauce;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\AggregateRootRepository as EventSauceAggregateRootRepository;
use EventSauce\EventSourcing\ConstructingAggregateRootRepository;
use EventSauce\EventSourcing\MessageDispatcherChain;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use EventSauce\EventSourcing\SynchronousMessageDispatcher;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\DatabaseManager;
use LogicException;

abstract class AggregateRootRepository implements EventSauceAggregateRootRepository
{
    /** @var Container */
    private $container;

    /** @var DatabaseManager */
    private $database;

    /** @var MessageSerializer */
    private $messageSerializer;

    /** @var EventSauceAggregateRootRepository */
    private $repository;

    /** @var string */
    protected $aggregateRoot;

    /** @var array */
    protected $syncConsumers = [];

    /** @var array */
    protected $asyncConsumers = [];

    /** @var string|null */
    protected $connection;

    /** @var string */
    protected $table = 'domain_messages';

    public function __construct(
        Container $container,
        DatabaseManager $database,
        MessageSerializer $messageSerializer
    ) {
        if ($this->aggregateRoot === null) {
            throw new LogicException("You have to set an aggregate root before the repository can be initialized.");
        }

        $this->container = $container;
        $this->database = $database;
        $this->messageSerializer = $messageSerializer;
        $this->repository = $this->buildRepository();
    }

    public function retrieve(AggregateRootId $aggregateRootId): object
    {
        return $this->repository->retrieve($aggregateRootId);
    }

    public function persist(object $aggregateRoot)
    {
        $this->repository->persist($aggregateRoot);
    }

    public function persistEvents(AggregateRootId $aggregateRootId, int $aggregateRootVersion, object ...$events)
    {
        $this->repository->persistEvents($aggregateRootId, $aggregateRootVersion, ...$events);
    }

    private function buildRepository(): EventSauceAggregateRootRepository
    {
        return new ConstructingAggregateRootRepository(
            $this->aggregateRoot,
            $this->messageRepository(),
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

    private function messageRepository(): MessageRepository
    {
        return new LaravelMessageRepository(
            $this->connection(),
            $this->messageSerializer,
            $this->table
        );
    }

    private function connection(): ConnectionInterface
    {
        return $this->database->connection($this->connection);
    }

    private function resolveConsumers(array $consumers)
    {
        return array_map(function (string $consumer) {
            return $this->container->make($consumer);
        }, $consumers);
    }
}
