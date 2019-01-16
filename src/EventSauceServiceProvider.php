<?php

declare(strict_types=1);

namespace EventSauce\LaravelEventSauce;

use EventSauce\EventSourcing\ConstructingAggregateRootRepository;
use EventSauce\EventSourcing\MessageDispatcherChain;
use EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use EventSauce\EventSourcing\SynchronousMessageDispatcher;
use EventSauce\LaravelEventSauce\Commands\GenerateCodeCommand;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;

final class EventSauceServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/eventsauce.php' => config_path('eventsauce.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'migrations');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/eventsauce.php', 'eventsauce');

        $this->commands([
            GenerateCodeCommand::class,
        ]);

        $this->registerAggregateRoots();
        $this->registerSynchronousDispatcher();
        $this->registerAsyncDispatcher();
        $this->registerMessageSerializer();

        $this->bindAsyncDispatcherToJob();
    }

    private function registerAggregateRoots(): void
    {
        foreach (config('eventsauce.aggregate_roots') as $aggregateRootConfig) {
            $this->app->bind($aggregateRootConfig['repository'], function () use ($aggregateRootConfig) {
                return new ConstructingAggregateRootRepository(
                    $aggregateRootConfig['aggregate_root'],
                    app(config('eventsauce.repository')),
                    new MessageDispatcherChain(
                        app(config('eventsauce.dispatcher')),
                        app(SynchronousMessageDispatcher::class)
                    )
                );
            });
        }
    }

    private function registerSynchronousDispatcher(): void
    {
        $this->app->bind(SynchronousMessageDispatcher::class, function () {
            $consumers = array_map(function ($consumerName) {
                return app($consumerName);
            }, $this->getConfigForAllAggregateRoots('sync_consumers'));

            return new SynchronousMessageDispatcher(...$consumers);
        });
    }

    private function registerAsyncDispatcher(): void
    {
        $this->app->bind('eventsauce.async_dispatcher', function () {
            $consumers = array_map(function ($consumerName) {
                return app($consumerName);
            }, $this->getConfigForAllAggregateRoots('async_consumers'));

            return new SynchronousMessageDispatcher(...$consumers);
        });
    }

    private function getConfigForAllAggregateRoots(string $key): array
    {
        $result = data_get(config('eventsauce'), "aggregate_roots.*.{$key}");

        return array_flatten($result);
    }

    private function registerMessageSerializer(): void
    {
        $this->app->bind(MessageSerializer::class, function () {
            return new ConstructingMessageSerializer();
        });
    }

    private function bindAsyncDispatcherToJob(): void
    {
        $this->app->bindMethod(EventSauceJob::class . '@handle', function (EventSauceJob $job, Container $container) {
            $dispatcher = $container->make('eventsauce.async_dispatcher');

            $job->handle($dispatcher);
        });
    }

    public function provides()
    {
        return [
            GenerateCodeCommand::class,
        ];
    }
}
