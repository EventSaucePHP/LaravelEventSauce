<?php

namespace Tests\Console;

use function class_exists;
use function config;
use EventSauce\EventSourcing\CodeGeneration\CodeDumper;
use EventSauce\LaravelEventSauce\Exceptions\CodeGenerationFailed;
use Tests\TestCase;

class FailingGenerateCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (class_exists(CodeDumper::class)) {
            self::markTestSkipped('This test should only run if the code generation package is not installed.');
        }
    }

    /**
     * @test
     */
    public function not_being_able_to_generate_code(): void
    {
        config(['eventsauce.repositories' => [
            UndefinedDefinitionFileRepository::class,
        ]]);

        $this->expectExceptionObject(CodeGenerationFailed::codeGenerationNotInstalled());

        $this->artisan('eventsauce:generate');
    }
}
