<?php

namespace Tests\TestTools;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\LaravelEventSauce\TestTools\AggregateRootTestCase;
use Tests\Fixtures\RegisterUser;
use Tests\Fixtures\RegistrationAggregateRoot;
use Tests\Fixtures\RegistrationAggregateRootId;
use Tests\Fixtures\UserWasRegistered;

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

    public function handle(object $command)
    {
        if($command instanceof RegisterUser) {
            /** @var RegistrationAggregateRoot $aggregate */
            $aggregate = $this->repository->retrieve($this->aggregateRootId);
            $aggregate->registerUser($command);
            $this->repository->persist($aggregate);
        }
    }

    /** @test */
    public function itInitializes()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function whenThenWorks()
    {
        $this->when(new RegisterUser('Foo', 'john@example.com'))
             ->then(new UserWasRegistered('Foo', 'john@example.com'));
    }

    /** @test */
    public function whenThenNothingShouldHaveHappenedWorks()
    {
        $this->when(new UserWasRegistered('Foo', 'john@example.com'))
            ->thenNothingShouldHaveHappened();
    }
}