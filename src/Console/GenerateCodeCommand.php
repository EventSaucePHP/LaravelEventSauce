<?php

declare(strict_types=1);

namespace EventSauce\LaravelEventSauce\Console;

use EventSauce\EventSourcing\CodeGeneration\CodeDumper;
use EventSauce\EventSourcing\CodeGeneration\YamlDefinitionLoader;
use EventSauce\LaravelEventSauce\Exceptions\InvalidConfiguration;
use Illuminate\Console\Command;

final class GenerateCodeCommand extends Command
{
    protected $signature = 'eventsauce:generate';

    protected $description = 'Generate EventSauce code.';

    public function handle()
    {
        $this->info('Start generating code...');

        $codeGenerationConfig = data_get(config('eventsauce'), 'aggregate_roots.*.code_generation');

        collect($codeGenerationConfig)->each(function (array $config) {
            $this->generateCode($config['input_yaml_file'], $config['output_file']);
        });

        $this->info('All done!');
    }

    private function generateCode(string $inputFile, string $outputFile)
    {
        if ( ! file_exists($inputFile)) {
            throw InvalidConfiguration::definitionFileDoesNotExist($inputFile);
        }

        $loader = new YamlDefinitionLoader();
        $dumper = new CodeDumper();

        $loadedYamlContent = $loader->load($inputFile);

        $phpCode = $dumper->dump($loadedYamlContent);

        file_put_contents($outputFile, $phpCode);

        $this->warn("Written code to `{$outputFile}`");
    }
}
