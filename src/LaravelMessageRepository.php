<?php

declare(strict_types=1);

namespace EventSauce\LaravelEventSauce;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\OffsetCursor;
use EventSauce\EventSourcing\PaginationCursor;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use Exception;
use Generator;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\DatabaseManager;
use Ramsey\Uuid\Uuid;

final class LaravelMessageRepository implements MessageRepository
{
    private DatabaseManager $database;

    private MessageSerializer $serializer;

    private string $connection;

    private string $table;

    public function __construct(DatabaseManager $database, MessageSerializer $serializer, Config $config)
    {
        $this->database = $database;
        $this->serializer = $serializer;
        $this->connection = (string) $config->get('eventsauce.connection');
        $this->table = (string) $config->get('eventsauce.table');
    }

    public function persist(Message ...$messages): void
    {
        $connection = $this->connection();

        collect($messages)->map(function (Message $message) {
            return $this->serializer->serializeMessage($message);
        })->each(function (array $message) use ($connection) {
            $headers = $message['headers'];

            $connection->table($this->table)->insert([
                'event_id' => $headers[Header::EVENT_ID] ?? Uuid::uuid4()->toString(),
                'event_type' => $headers[Header::EVENT_TYPE],
                'event_stream' => $headers[Header::AGGREGATE_ROOT_ID] ?? null,
                'recorded_at' => $headers[Header::TIME_OF_RECORDING],
                'payload' => json_encode($message),
            ]);
        });
    }

    public function retrieveAll(AggregateRootId $id): Generator
    {
        $payloads = $this->connection()->table($this->table)
            ->where('event_stream', $id->toString())
            ->orderBy('recorded_at')
            ->get('payload');

        foreach ($payloads as $payload) {
            $messages = $this->serializer->unserializePayload(json_decode($payload->payload, true));

            if ($messages instanceof Message) {
                yield $messages;
            } else {
                yield from $messages;
            }
        }

        return $payloads->count();
    }

    /**
     * @throws \Exception
     */
    public function retrieveAllAfterVersion(AggregateRootId $id, int $aggregateRootVersion): Generator
    {
        throw new Exception('Snapshotting not supported yet.');
    }

    private function connection(): ConnectionInterface
    {
        return $this->database->connection($this->connection);
    }

    public function setConnection(string $connection): void
    {
        $this->connection = $connection;
    }

    public function setTable(string $table): void
    {
        $this->table = $table;
    }

    public function paginate(PaginationCursor|OffsetCursor $cursor): Generator
    {
        $payloads = $this->connection()->table($this->table)
            ->orderBy('recorded_at')
            ->offset($cursor->offset())
            ->limit($cursor->limit())
            ->get('payload');

        foreach ($payloads as $payload) {
            $messages = $this->serializer->unserializePayload(json_decode($payload->payload, true));

            if ($messages instanceof Message) {
                yield $messages;
            } else {
                yield from $messages;
            }
        }

        return $payloads->count();
    }
}
