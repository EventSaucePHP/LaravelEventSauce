<?php

declare(strict_types=1);

namespace EventSauce\LaravelEventSauce;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use Generator;
use Illuminate\Database\ConnectionInterface as Connection;
use Ramsey\Uuid\Uuid;

final class LaravelMessageRepository implements MessageRepository
{
    /** @var Connection */
    private $connection;

    /** @var MessageSerializer */
    private $serializer;

    /** @var string */
    private $table;

    public function __construct(
        Connection $connection,
        MessageSerializer $serializer,
        string $table = 'domain_messages'
    ) {
        $this->serializer = $serializer;
        $this->connection = $connection;
        $this->table = $table;
    }

    public function persist(Message ...$messages)
    {
        foreach ($messages as $message) {
            $serialized = $this->serializer->serializeMessage($message);

            $this->connection->table($this->table)->insert([
                'event_id' => $serialized['headers'][Header::EVENT_ID] ?? Uuid::uuid4()->toString(),
                'event_type' => $serialized['headers'][Header::EVENT_TYPE],
                'aggregate_root_id' => $serialized['headers'][Header::AGGREGATE_ROOT_ID] ?? null,
                'recorded_at' => $serialized['headers'][Header::TIME_OF_RECORDING],
                'payload' => json_encode($serialized),
            ]);
        }
    }

    public function retrieveAll(AggregateRootId $id): Generator
    {
        $payloads = $this->connection->table($this->table)
            ->where('aggregate_root_id', $id->toString())
            ->orderBy('recorded_at')
            ->get('payload');

        foreach ($payloads as $payload) {
            yield from $this->serializer->unserializePayload(json_decode($payload->payload, true));
        }
    }
}
