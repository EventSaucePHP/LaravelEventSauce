<?php

declare(strict_types=1);

namespace EventSauce\LaravelEventSauce;

use EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use EventSauce\LaravelEventSauce\Console\GenerateCommand;
use Illuminate\Support\ServiceProvider;

final class EventSauceServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

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
            GenerateCommand::class,
        ]);

        $this->app->bind(MessageSerializer::class, function () {
            return new ConstructingMessageSerializer();
        });
    }

    public function provides()
    {
        return [
            GenerateCommand::class,
        ];
    }
}
