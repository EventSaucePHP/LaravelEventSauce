<?php

declare(strict_types=1);

namespace EventSauce\LaravelEventSauce\Console;

use EventSauce\LaravelEventSauce\Exceptions\MakeFileFailed;

final class MakeConsumerCommand extends MakeCommand
{
    protected $signature = 'make:consumer {class}';

    protected $description = 'Create a new consumer class';

    public function handle()
    {
        $consumerClass = $this->formatClassName($this->argument('class'));
        $consumerPath = $this->getPath($consumerClass);

        try {
            $this->ensureValidPaths([
                $consumerPath,
            ]);
        } catch (MakeFileFailed $exception) {
            return 1;
        }

        $this->makeDirectory($consumerPath);

        $this->makeFiles(
            ['Consumer' => $consumerPath],
            [
                'consumer' => class_basename($consumerClass),
                'namespace' => substr($consumerClass, 0, strrpos($consumerClass, '\\')),
            ],
        );

        $this->info("{$consumerClass} class created successfully!");
    }
}
