<?php

declare(strict_types=1);

namespace EventSauce\LaravelEventSauce;

use EventSauce\EventSourcing\EventSourcedAggregateRootRepository;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\MessageRepository\IlluminateMessageRepository\IlluminateUuidV4MessageRepository;
use EventSauce\MessageRepository\TableSchema\TableSchema;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Support\Facades\DB;
use function array_unshift;
use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\AggregateRootRepository as EventSauceAggregateRootRepository;
use EventSauce\EventSourcing\DefaultHeadersDecorator;
use EventSauce\EventSourcing\MessageDecoratorChain;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\MessageDispatcherChain;
use function in_array;
use LogicException;
use function resolve;

abstract class AggregateRootRepository implements EventSauceAggregateRootRepository
{

    protected string $aggregateRoot = '';

    protected array $consumers = [];

    protected array $decorators = [];

    protected string $connection = '';

    protected string $table = '';

    protected string $queue = '';

    protected static string $inputFile = '';

    protected static string $outputFile = '';

    protected MessageRepository $messageRepository;

    public function __construct(
        protected Config $config,
        ?MessageRepository $messageRepository = null
    )
    {
        if (! is_a($this->aggregateRoot, AggregateRoot::class, true)) {
            throw new LogicException('You have to set an aggregate root before the repository can be initialized.');
        }

        $this->messageRepository = $messageRepository ?? $this->constructMessageRepository();

    }

    private function constructMessageRepository(): MessageRepository
    {
        return resolve(IlluminateUuidV4MessageRepository::class, [
            'connection' => DB::connection($this->connection ?: (string) $this->config->get('eventsauce.connection')),
            'tableName' => $this->table ?: (string) $this->config->get('eventsauce.table'),
            'tableSchema' => $this->getTableSchema(),
        ]);
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
        return new EventSourcedAggregateRootRepository(
            $this->aggregateRoot,
            $this->messageRepository,
            new MessageDispatcherChain(
                $this->buildLaravelMessageDispatcher(),
                new EventMessageDispatcher(),
            ),
            new MessageDecoratorChain(...$this->buildMessageDecorators()),
        );
    }

    protected function getTableSchema(): TableSchema
    {
        return new LaravelEventSauceTableSchema();
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
