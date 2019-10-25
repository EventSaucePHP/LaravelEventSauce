<?php

namespace Tests\TestTools;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\LaravelEventSauce\TestTools\AggregateRootTestCase;
use Tests\Fixtures\RegistrationAggregateRoot;
use Tests\Fixtures\RegistrationAggregateRootId;

class AggregateRootTestCaseTest extends AggregateRootTestCase
{
    protected function newAggregateRootId(): AggregateRootId
    {
        return RegistrationAggregateRootId::create();
    }

    protected function aggregateRootClassName(): string
    {
        return RegistrationAggregateRoot::class;
    }

    /** @test */
    public function itInitializes()
    {
        $this->assertTrue(true);
    }
}