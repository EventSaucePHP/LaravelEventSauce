<?php

declare(strict_types=1);

namespace EventSauce\LaravelEventSauce;

use EventSauce\EventSourcing\AggregateRoot as EventSauceAggregateRoot;
use EventSauce\EventSourcing\AggregateRootBehaviour;

abstract class AggregateRoot implements EventSauceAggregateRoot
{
    use AggregateRootBehaviour;
}
