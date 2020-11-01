<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use DomainException;
use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootBehaviour;

final class RegistrationAggregateRoot implements AggregateRoot
{
    use AggregateRootBehaviour;

    private array $registered = [];

    public function registerUser(RegisterUser $command): void
    {
        if (in_array($email = $command->email(), $this->registered)) {
            throw new DomainException("A user with email address \"$email\" was already registered.");
        }

        $this->recordThat(
            new UserWasRegistered($command->name(), $email),
        );
    }

    public function applyUserWasRegistered(UserWasRegistered $event): void
    {
        $this->registered[] = $event->email();
    }
}
