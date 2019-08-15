<?php

declare(strict_types=1);

namespace EventSauce\LaravelEventSauce\Console;

final class MakeConsumerCommand extends MakeCommand
{
    protected $signature = 'make:consumer {class}';

    protected $description = 'Create a new consumer class';

    public function handle(): void
    {
        $consumerClass = $this->formatClassName($this->argument('class'));
        $consumerPath = $this->getPath($consumerClass);

        $this->ensureValidPaths([
            $consumerPath,
        ]);

        $this->makeDirectory($consumerPath);

        $this->makeFiles(
            ['Consumer' => $consumerPath],
            [
                'consumer' => class_basename($consumerClass),
                'namespace' => substr($consumerClass, 0, strrpos($consumerClass, '\\')),
            ]
        );

        $this->info('Consumer class created successfully!');
    }
}
