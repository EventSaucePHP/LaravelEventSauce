<?php

namespace EventSauce\LaravelEventSauce;

use EventSauce\EventSourcing\CodeGeneration\CodeDumper;
use EventSauce\EventSourcing\CodeGeneration\DefinitionGroup;
use EventSauce\EventSourcing\CodeGeneration\YamlDefinitionLoader;
use Illuminate\Console\Command;
use function file_put_contents;

final class GenerateCodeCommand extends Command
{
    protected $signature = 'eventsauce:generate-code';
    protected $description = 'Generate EventSauce Code.';

    public function handle()
    {
        $definitionPath = (array) config('eventsauce.definition');
        $outputPath = config('eventsauce.output');
        $loader = new YamlDefinitionLoader();
        $definitionGroup = new DefinitionGroup();
        $dumper = new CodeDumper();

        foreach ($definitionPath as $path) {
            $definitionGroup = $loader->load($path, $definitionGroup);
        }

        file_put_contents($outputPath, $dumper->dump($definitionGroup));
        $this->output->writeln('Code generated!');
    }
}
