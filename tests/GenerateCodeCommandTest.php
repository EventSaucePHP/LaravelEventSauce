<?php

declare(strict_types=1);

namespace Tests;

use EventSauce\LaravelEventSauce\AggregateRootRepository;
use EventSauce\LaravelEventSauce\Exceptions\CodeGenerationFailed;
use Tests\Fixtures\RegistrationAggregateRootRepository;

class GenerateCodeCommandTest extends TestCase
{
    /** @test */
    public function it_can_generate_eventsauce_code()
    {
        config(['eventsauce.code_generation' => [
            RegistrationAggregateRootRepository::class,
        ]]);

        $this->artisan('eventsauce:generate');

        $this->assertFileExists(__DIR__ . '/Fixtures/commands_and_events.php');
    }

    /** @test */
    public function it_throws_an_exception_when_the_definition_file_cannot_be_found()
    {
        config(['eventsauce.code_generation' => [
            UndefinedDefinitionFileRepository::class,
        ]]);

        $this->expectException(CodeGenerationFailed::class);

        $this->artisan('eventsauce:generate');
    }
}

class UndefinedDefinitionFileRepository extends AggregateRootRepository
{
    /** @var string */
    protected static $inputFile = 'foo.php';
}
