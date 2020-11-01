<?php

declare(strict_types=1);

namespace Tests;

use EventSauce\LaravelEventSauce\AggregateRootRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use LogicException;
use Tests\Fixtures\RegisterUser;
use Tests\Fixtures\RegistrationAggregateRoot;
use Tests\Fixtures\RegistrationAggregateRootId;
use Tests\Fixtures\RegistrationAggregateRootRepository;

class AggregateRootRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('event_store', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('event_id', 36);
            $table->string('event_type', 100);
            $table->string('event_stream', 36)->nullable()->index();
            $table->dateTime('recorded_at', 6)->index();
            $table->text('payload');
        });

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_throws_an_exception_without_an_aggregate_root_property()
    {
        $this->expectException(LogicException::class);

        $this->repository(RepositoryWithoutAggregateRootProperty::class);
    }

    /** @test */
    public function it_throws_an_exception_with_a_non_aggregate_root()
    {
        $this->expectException(LogicException::class);

        $this->repository(RepositoryWithNonAggregateRoot::class);
    }

    /** @test */
    public function it_can_retrieve_an_aggregate()
    {
        $repository = $this->repository(RegistrationAggregateRootRepository::class);

        $registration = $repository->retrieve(RegistrationAggregateRootId::create());

        $this->assertInstanceOf(RegistrationAggregateRoot::class, $registration);
    }

    /** @test */
    public function it_can_persist_an_aggregate()
    {
        $this->persistAggregate(RegistrationAggregateRootRepository::class);

        $this->assertDatabaseHas('domain_messages', [
            'id' => 1,
            'event_type' => 'tests.fixtures.user_was_registered',
        ]);
    }

    /** @test */
    public function it_can_dispatch_its_consumers()
    {
        $this->persistAggregate(RegistrationAggregateRootRepository::class);

        $this->assertDatabaseHas('users', ['name' => 'Dries Vints', 'email' => 'dries.vints@gmail.com']);
    }

    /**
     * @test
     *
     * @todo Test this against non-in memory database connection.
     */
    public function it_can_have_a_custom_connection()
    {
        $connection = 'custom';

        config(["database.connections.$connection" => [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]]);

        $this->artisan('migrate', ['--database' => $connection]);

        $this->persistAggregate(RepositoryWithCustomConnection::class);

        $this->assertDatabaseHas('domain_messages', [
            'id' => 1,
            'event_type' => 'tests.fixtures.user_was_registered',
        ], $connection);
    }

    /** @test */
    public function it_can_have_a_custom_table()
    {
        $this->persistAggregate(RepositoryWithCustomTable::class);

        $this->assertDatabaseHas('event_store', [
            'id' => 1,
            'event_type' => 'tests.fixtures.user_was_registered',
        ]);
    }

    private function persistAggregate(string $repository): void
    {
        $repository = $this->repository($repository);

        $registration = $repository->retrieve(RegistrationAggregateRootId::create());

        $registration->registerUser(
            new RegisterUser('Dries Vints', 'dries.vints@gmail.com'),
        );

        $repository->persist($registration);
    }

    private function repository(string $repository): AggregateRootRepository
    {
        return $this->app->make($repository);
    }
}

final class RepositoryWithoutAggregateRootProperty extends AggregateRootRepository
{
}

final class RepositoryWithNonAggregateRoot extends AggregateRootRepository
{
    protected string $aggregateRoot = Foo::class;
}

final class Foo
{
}

final class RepositoryWithCustomConnection extends AggregateRootRepository
{
    protected string $aggregateRoot = RegistrationAggregateRoot::class;

    protected string $connection = 'custom';
}

final class RepositoryWithCustomTable extends AggregateRootRepository
{
    protected string $aggregateRoot = RegistrationAggregateRoot::class;

    protected string $table = 'event_store';
}
