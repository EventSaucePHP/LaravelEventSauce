<?php

declare(strict_types=1);

namespace EventSauce\LaravelEventSauce\Console;

use EventSauce\EventSourcing\CodeGeneration\CodeDumper;
use EventSauce\EventSourcing\CodeGeneration\YamlDefinitionLoader;
use EventSauce\LaravelEventSauce\Exceptions\CodeGenerationFailed;
use Illuminate\Console\Command;

final class GenerateCommand extends Command
{
    protected $signature = 'eventsauce:generate';

    protected $description = 'Generate aggregate commands and events.';

    public function handle(): void
    {
        $this->info('Start generating code...');

        $codeGenerationConfig = config('eventsauce.code_generation');

        collect($codeGenerationConfig)
            ->reject(function (string $repository) {
                return $repository::inputFile() === '';
            })
            ->each(function (string $repository) {
                $this->generateCode($repository::inputFile(), $repository::outputFile());
            });

        $this->info('All done!');
    }

    private function generateCode(string $inputFile, string $outputFile): void
    {
        if (! file_exists($inputFile)) {
            throw CodeGenerationFailed::definitionFileDoesNotExist($inputFile);
        }

        $loader = new YamlDefinitionLoader();
        $dumper = new CodeDumper();

        $loadedYamlContent = $loader->load($inputFile);

        $phpCode = $dumper->dump($loadedYamlContent);

        file_put_contents($outputFile, $phpCode);

        $this->warn("Written code to `{$outputFile}`");
    }
}
