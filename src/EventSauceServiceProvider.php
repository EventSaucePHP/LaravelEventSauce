<?php

declare(strict_types=1);

namespace EventSauce\LaravelEventSauce;

use EventSauce\EventSourcing\AggregateRootRepository;
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
                __DIR__.'/../config/eventsauce.php' => config_path('eventsauce.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'migrations');
        }
    }

    public function register()
    {
        $this->commands([
            GenerateCodeCommand::class,
        ]);

        $this->mergeConfigFrom(
            __DIR__ . '/../config/eventsauce.php', 'eventsauce'
        );

        foreach (config('eventsauce.aggregate_roots') as $aggregateRootConfig) {
            $this->app->bind($aggregateRootConfig['repository'], function (Container $app) use ($aggregateRootConfig) {
                return new ConstructingAggregateRootRepository(
                    $aggregateRootConfig['class'],
                    $app->make(LaravelMessageRepository::class),
                    new MessageDispatcherChain(
                        $app->make(LaravelMessageDispatcher::class),
                        $app->make(SynchronousMessageDispatcher::class)
                    )
                );
            });

            $this->app->bind(SynchronousMessageDispatcher::class, function (Container $app) use ($aggregateRootConfig) {
                $consumers = array_map(function ($consumerName) use ($app) {
                    return $app->make($consumerName);
                }, $aggregateRootConfig['sync_consumers']);

                return new SynchronousMessageDispatcher(...$consumers);
            });

            $this->app->bind('eventsauce.async_dispatcher', function (Container $app) use ($aggregateRootConfig) {
                $consumers = array_map(function ($consumerName) use ($app) {
                    return $app->make($consumerName);
                }, $aggregateRootConfig['async_consumers']);

                return new SynchronousMessageDispatcher(...$consumers);
            });
        }

        $this->app->bind(MessageSerializer::class, function () {
            return new ConstructingMessageSerializer();
        });

        $this->app->bindMethod(EventSauceJob::class . '@handle', function (EventSauceJob $job, Container $container) {
            $dispatcher = $container->make('eventsauce.async_dispatcher');

            return $job->handle($dispatcher);
        });
    }

    public function provides()
    {
        return [
            GenerateCodeCommand::class,
        ];
    }
}
