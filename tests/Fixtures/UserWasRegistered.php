<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use EventSauce\EventSourcing\Serialization\SerializableEvent;

final class UserWasRegistered implements SerializableEvent
{
    /** @var string */
    private $name;

    /** @var string */
    private $email;

    public function __construct(string $name, string $email)
    {
        $this->name = $name;
        $this->email = $email;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function toPayload(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
        ];
    }

    public static function fromPayload(array $payload): SerializableEvent
    {
        return new static(
            $payload['name'],
            $payload['email']
        );
    }
}