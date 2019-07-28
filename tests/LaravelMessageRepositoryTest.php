<?php

declare(strict_types=1);

namespace Tests;

use EventSauce\LaravelEventSauce\LaravelMessageRepository;
use Tests\Fixtures\RegisterUser;
use Tests\Fixtures\RegistrationAggregateRootId;
use Tests\Fixtures\RegistrationAggregateRootRepository;
use Tests\Fixtures\UserWasRegistered;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LaravelMessageRepositoryTest extends TestCase
{
    use RefreshDatabase;

    /** @var LaravelMessageRepository */
    private $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->app->make(LaravelMessageRepository::class);
    }

    public function tearDown(): void
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
        $this->registerUser(
            $aggregateRootId = RegistrationAggregateRootId::create()
        );

        foreach ($this->repository->retrieveAll($aggregateRootId) as $message) {
            $this->assertInstanceOf(UserWasRegistered::class, $message->event());
        }
    }

    private function registerUser(RegistrationAggregateRootId $aggregateRootId): void
    {
        $repository = $this->aggregateRepository();

        $registration = $repository->retrieve($aggregateRootId);

        $registration->registerUser(
            new RegisterUser('Dries Vints', 'driesvints@gmail.com')
        );

        $repository->persist($registration);
    }

    private function aggregateRepository(): RegistrationAggregateRootRepository
    {
        return $this->app->make(RegistrationAggregateRootRepository::class);
    }
}
