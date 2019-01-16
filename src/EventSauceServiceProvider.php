<?php

declare(strict_types=1);

namespace EventSauce\LaravelEventSauce;

use EventSauce\EventSourcing\ConstructingAggregateRootRepository;
use EventSauce\EventSourcing\MessageDispatcherChain;
use EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use EventSauce\EventSourcing\SynchronousMessageDispatcher;
use EventSauce\LaravelEventSauce\Commands\GenerateCodeCommand;
use Illuminate\Contracts\Container\Container;
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

        $this->registerSynchronousDispatcher();
        $this->registerAsyncDispatcher();
        $this->registerMessageDispatcherChain();
        $this->registerAggregateRoots();
        $this->registerMessageSerializer();

        $this->bindAsyncDispatcherToJob();
    }

    private function registerSynchronousDispatcher(): void
    {
        $this->app->bind(SynchronousMessageDispatcher::class, function (Container $container) {
            $config = $container->make('config')->get('eventsauce');

            $consumers = array_map(function ($consumerName) use ($container) {
                return $container->make($consumerName);
            }, $this->getConfigForAllAggregateRoots($config, 'sync_consumers'));

            return new SynchronousMessageDispatcher(...$consumers);
        });
    }

    private function registerAsyncDispatcher(): void
    {
        $this->app->bind('eventsauce.async_dispatcher', function (Container $container) {
            $config = $container->make('config')->get('eventsauce');

            $consumers = array_map(function ($consumerName) use ($container) {
                return $container->make($consumerName);
            }, $this->getConfigForAllAggregateRoots($config, 'async_consumers'));

            return new SynchronousMessageDispatcher(...$consumers);
        });
    }

    private function getConfigForAllAggregateRoots(array $config, string $key): array
    {
        $result = data_get($config, "aggregate_roots.*.{$key}");

        return array_flatten($result);
    }

    private function registerMessageDispatcherChain(): void
    {
        $this->app->bind(MessageDispatcherChain::class, function (Container $container)  {
            $dispatcher = $container->make('config')->get('eventsauce.dispatcher');

            return new MessageDispatcherChain(
                $container->make($dispatcher),
                $container->make(SynchronousMessageDispatcher::class)
            );
        });
    }

    private function registerAggregateRoots(): void
    {
        $config = $this->app->make('config')->get('eventsauce');

        foreach ($config['aggregate_roots'] as $aggregateRootConfig) {
            $this->app->bind(
                $aggregateRootConfig['repository'],
                function (Container $container) use ($aggregateRootConfig, $config) {
                    return new ConstructingAggregateRootRepository(
                        $aggregateRootConfig['aggregate_root'],
                        $container->make($config['repository']),
                        $container->make(MessageDispatcherChain::class)
                    );
                }
            );
        }
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
