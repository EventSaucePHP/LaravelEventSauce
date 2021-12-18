<?php

declare(strict_types=1);

namespace Tests\Console;

use EventSauce\EventSourcing\CodeGeneration\CodeDumper;
use EventSauce\LaravelEventSauce\AggregateRootRepository;
use EventSauce\LaravelEventSauce\Exceptions\CodeGenerationFailed;
use Tests\Fixtures\RegistrationAggregateRootRepository;
use Tests\TestCase;

use function class_exists;

class GenerateCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if ( ! class_exists(CodeDumper::class)) {
            self::markTestSkipped('eventsauce/code-generation is not installed');
        }
    }

    /** @test */
    public function it_can_generate_eventsauce_code()
    {
        config(['eventsauce.repositories' => [
            RegistrationAggregateRootRepository::class,
        ]]);

        $this->artisan('eventsauce:generate');

        $this->assertFileExists(__DIR__.'/../Fixtures/commands_and_events.php');
    }

    /** @test */
    public function it_throws_an_exception_when_the_definition_file_cannot_be_found()
    {
        config(['eventsauce.repositories' => [
            UndefinedDefinitionFileRepository::class,
        ]]);

        $this->expectException(CodeGenerationFailed::class);

        $this->artisan('eventsauce:generate');
    }
}

class UndefinedDefinitionFileRepository extends AggregateRootRepository
{
    protected static string $inputFile = 'foo.php';
}
