<?php

declare(strict_types=1);

namespace EventSauce\LaravelEventSauce;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use Generator;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

final class LaravelMessageRepository implements MessageRepository
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var MessageSerializer
     */
    private $serializer;

    public function __construct(Connection $connection, MessageSerializer $serializer)
    {
        $this->serializer = $serializer;
        $this->connection = $connection;
    }

    public function persist(Message ... $messages)
    {
        foreach ($messages as $message) {
            $serialized = $this->serializer->serializeMessage($message);
            $eventId = $serialized['headers'][Header::EVENT_ID] ?? Uuid::uuid4()->toString();
            $type = $serialized['headers'][Header::EVENT_TYPE];
            $payload = json_encode($serialized);
            $timeOfRecording = $serialized['headers'][Header::TIME_OF_RECORDING];
            $aggregateRootId = $serialized['headers'][Header::AGGREGATE_ROOT_ID] ?? null;
            DB::insert("INSERT INTO domain_messages
                          (event_id, event_type, aggregate_root_id, time_of_recording, payload)
                          VALUES (?, ?, ?, ?, ?)
            ", [$eventId, $type, $aggregateRootId, $timeOfRecording, $payload]);
        }
    }

    public function retrieveAll(AggregateRootId $id): Generator
    {
        $payloads = DB::select(
            'SELECT payload FROM domain_messages WHERE aggregate_root_id = ? ORDER BY time_of_recording ASC',
            [$id->toString()]
        );

        foreach ($payloads as $payload) {
            yield from $this->serializer->unserializePayload(json_decode($payload->payload, true));
        }
    }
}
