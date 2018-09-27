<?php

namespace EventSauce\LaravelEventSauce;

use EventSauce\EventSourcing\AggregateRootRepository;
use EventSauce\EventSourcing\ConstructingAggregateRootRepository;
use EventSauce\EventSourcing\MessageDispatcherChain;
use EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use EventSauce\EventSourcing\SynchronousMessageDispatcher;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;

class EventSauceServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(AggregateRootRepository::class, function (Container $app) {
            return new ConstructingAggregateRootRepository(
                $app['config']->get('eventsauce.aggregate_root'),
                $app->make(LaravelMessageRepository::class),
                new MessageDispatcherChain(
                    $app->make(LaravelMessageDispatcher::class),
                    $app->make(SynchronousMessageDispatcher::class)
                )
            );
        });

        $this->app->bind(SynchronousMessageDispatcher::class, function (Container $app) {
            $consumers = array_map(function ($consumerName) use ($app) {
                return $app->make($consumerName);
            }, $app['config']->get('eventsauce.sync_consumers'));

            return new SynchronousMessageDispatcher(... $consumers);
        });

        $this->app->bind('eventsauce.async_dispatcher', function (Container $app) {
            $consumers = array_map(function ($consumerName) use ($app) {
                return $app->make($consumerName);
            }, $app['config']->get('eventsauce.async_consumers'));

            return new SynchronousMessageDispatcher(... $consumers);
        });

        $this->app->bind(MessageSerializer::class, function () {
            return new ConstructingMessageSerializer();
        });

        $this->app->bindMethod(EventSauceJob::class . '@handle', function (EventSauceJob $job, Container $container) {
            $dispatcher = $container->make('eventsauce.async_dispatcher');

            return $job->handle($dispatcher);
        });
    }
}
