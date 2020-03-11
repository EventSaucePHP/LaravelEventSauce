<?php

declare(strict_types=1);

namespace Tests;

use EventSauce\LaravelEventSauce\LaravelMessageRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fixtures\RegistrationAggregateRootId;
use Tests\Fixtures\UserWasRegistered;

class LaravelMessageRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private LaravelMessageRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->app->make(LaravelMessageRepository::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->repository);
    }

    /** @test */
    public function it_can_persists_messages()
    {
        $message = $this->getUserWasRegisteredMessage();

        $this->repository->persist($message);

        $this->assertDatabaseHas('domain_messages', [
            'id' => 1,
            'event_type' => 'tests.fixtures.user_was_registered',
        ]);
    }

    /** @test */
    public function it_can_retrieve_messages()
    {
        $id = RegistrationAggregateRootId::create();
        $message = $this->getUserWasRegisteredMessage($id);

        $this->repository->persist($message);

        foreach ($this->repository->retrieveAll($id) as $message) {
            $this->assertEquals($id, $message->aggregateRootId());
            $this->assertInstanceOf(UserWasRegistered::class, $message->event());
        }
    }
}
