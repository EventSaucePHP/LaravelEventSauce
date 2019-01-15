<?php

declare(strict_types=1);

namespace EventSauce\LaravelEventSauce\Commands;

use EventSauce\EventSourcing\CodeGeneration\CodeDumper;
use EventSauce\EventSourcing\CodeGeneration\DefinitionGroup;
use EventSauce\EventSourcing\CodeGeneration\YamlDefinitionLoader;
use EventSauce\LaravelEventSauce\Exceptions\InvalidConfiguration;
use http\Exception\InvalidArgumentException;
use Illuminate\Console\Command;

final class GenerateCodeCommand extends Command
{
    protected $signature = 'eventsauce:generate-code';
    
    protected $description = 'Generate EventSauce code.';

    public function handle()
    {
        $this->info('Start generating code...');

        $codeGenerationConfig = data_get(config('eventsauce'), 'aggregate_roots.*.code_generation');

        collect($codeGenerationConfig)->each(function(array $config) {
            $this->generateCode($config['definition_file'], $config['output_to_file']);
        });

        $this->info('All done!');
    }

    private function generateCode(string $definitionFile, string $outputFile)
    {
        if (! file_exists($definitionFile)) {
            throw InvalidConfiguration::definitionFileDoesNotExist($definitionFile);
        }

        $loader = new YamlDefinitionLoader();
        $dumper = new CodeDumper();

        $loadedDefinitionFile = $loader->load($definitionFile);

        $phpCode = $dumper->dump($loadedDefinitionFile);

        file_put_contents($outputFile, $phpCode);

        $this->warn("Written code to `{$outputFile}`");
    }
}
