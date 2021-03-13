<p align="center">
    <a href="https://eventsauce.io">
        <img src="https://eventsauce.io/static/logo.svg" height="100px" width="100px">
    </a>
</p>

<p align="center">
    <a href="https://github.com/EventSaucePHP/LaravelEventSauce/actions?query=workflow%3ATests">
        <img src="https://github.com/EventSaucePHP/LaravelEventSauce/workflows/Tests/badge.svg" alt="Build Status">
    </a>
    <a href="https://github.styleci.io/repos/146869722">
        <img src="https://github.styleci.io/repos/146869722/shield?style=flat" alt="Code Style">
    </a>
    <a href="https://packagist.org/packages/eventsauce/laravel-eventsauce">
        <img src="https://img.shields.io/packagist/v/eventsauce/laravel-eventsauce.svg" alt="Latest Stable Version">
    </a>
    <a href="https://packagist.org/packages/eventsauce/laravel-eventsauce">
        <img src="https://img.shields.io/packagist/dt/eventsauce/laravel-eventsauce.svg" alt="Total Downloads">
    </a>
</p>

# Laravel EventSauce

This library allows you to easily integrate [EventSauce](https://eventsauce.io) with your Laravel application. It takes out the tedious work of having to set up your own message dispatcher and provides an easy API to set up Aggregate Roots, Aggregate Root Repositories, Consumers, and more. It also comes with a range of scaffolding console commands to easily generate the boilerplate needed to get started with an Event Sourced application. 

Thanks to [Frank de Jonge](https://github.com/frankdejonge) for building and maintaining the main [EventSauce](https://eventsauce.io) project.

While already usable, this library is currently still a work in progress. More documentation and features will be added over time. We appreciate pull requests that help extend and improve this project.

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
    - [Migrations](#migrations)
    - [Default Connection](#default-connection)
    - [Default Table](#default-table)
- [Scaffolding](#scaffolding)
    - [Generating Aggregate Roots](#generating-aggregate-roots)
    - [Generating Consumers](#generating-consumers)
    - [Generating Commands & Events](#generating-commands--events)
- [Usage](#usage)
    - [Aggregate Roots](#aggregate-roots)
    - [Aggregate Root Repositories](#aggregate-root-repositories)
    - [Consumers](#consumers)
- [Changelog](#changelog)
- [Maintainers](#maintainers)
- [Acknowledgments](#acknowledgments)
- [License](#license)

## Requirements

- PHP 7.4 or higher
- Laravel 8.0 or higher

## Installation

Before installing a new package it's always a good idea to clear your config cache:

```bash
php artisan config:clear
```

You can install the library through [Composer](https://getcomposer.org). This will also install [the main EventSauce library](https://github.com/EventSaucePHP/EventSauce).

```bash
composer require eventsauce/laravel-eventsauce
```

## Configuration

You can publish the config file with the following command:

```bash
php artisan vendor:publish --tag="eventsauce-config"
```

### Migrations

The default `domain_messages` table will be loaded in through the library's service provider and migrated with:
 
```bash
php artisan migrate
```

You can also publish it and modify it as you see fit with the following command:

```bash
php artisan vendor:publish --tag="eventsauce-migrations"
```

### Default Connection

The default database connection can be modified by setting the `EVENTSAUCE_CONNECTION` env variable:

```dotenv
EVENTSAUCE_CONNECTION=mysql
```

### Default Table

The default table name for your domain messages can be set with the `EVENTSAUCE_TABLE` env variable:

```dotenv
EVENTSAUCE_TABLE=event_store
```

## Scaffolding

Laravel EventSauce comes with some commands that you can use to scaffold objects and files which you'll need to build your Event Sourced app. These commands take out the tedious work of writing these yourself and instead let you focus on actually writing your domain logic.

### Generating Aggregate Roots

Laravel EventSauce can generate aggregate roots and its related files for you. By using the `make:aggregate-root` command, you can generate the following objects and files:

- The `AggregateRoot`
- The `AggregateRootId`
- The `AggregateRootRepository`
- The migration file

To generate these files for a "Registration" process, run the following command:

```bash
php artisan make:aggregate-root Domain/Registration
```

This will scaffold the following files:

- `App\Domain\Registration`
- `App\Domain\RegistrationId`
- `App\Domain\RegistrationRepository`
- `database/migrations/xxxx_xx_xx_create_registration_domain_messages_table.php`

These are all the files you need to get started with an Aggregate Root.

### Generating Consumers

Laravel EventSauce can also generate consumers for you. For example, run the `make:consumer` command to generate a `SendEmailConfirmation` process manager:

```bash
php artisan make:consumer Domain/SendEmailConfirmation
```

This will create a class at `App\Domain\SendEmailConfirmation` where you can now define `handle{EventName}` methods to handle events.

### Generating Commands & Events

EventSauce can generate commands and events for you so you don't need to write these yourself. First, define a `commands_and_events.yml` file which contains your definitions:

```yaml
namespace: App\Domain\Registration
commands:
  ConfirmUser:
    fields:
      identifier: RegistrationAggregateRootId
      user_id: int
events:
  UserWasConfirmed:
    fields:
      identifier: RegistrationAggregateRootId
      user_id: int
```

Then define the input and output output file in the AggregateRootRepository:

```php
final class RegistrationAggregateRootRepository extends AggregateRootRepository
{
    ...

    /** @var string */
    protected static $inputFile = __DIR__.'/commands_and_events.yml';

    /** @var string */
    protected static $outputFile = __DIR__.'/commands_and_events.php';
}
```

And register the AggregateRootRepository in your `eventsauce.php` config file:

```php
'repositories' => [
    App\Domain\Registration\RegistrationAggregateRootRepository::class,
],
```

You can now generate commands and events for all repositories that you've added by running the following command:

```bash
php artisan eventsauce:generate
```

For more info on creating events and commands with EventSauce, as well as how to define different types, [see the EventSauce documentation](https://eventsauce.io/docs/event-sourcing/create-events-and-commands).

## Usage

### Aggregate Roots

More docs coming soon...

### Aggregate Root Repositories

More docs coming soon...

#### Queue Property

You can instruct Laravel to queue all consumers onto a specific queue by setting the `$queue` property:

```php
use App\Domain\SendConfirmationNotification;
use EventSauce\LaravelEventSauce\AggregateRootRepository;

final class RegistrationAggregateRootRepository extends AggregateRootRepository
{
    protected array $consumers = [
        SendConfirmationNotification::class,
    ];
    
    protected string $queue = 'registrations';
}
```

This will force all consumers who have the `ShouldQueue` contract implemented to make use of the `registrations` queue instead of the default queue defined in your `queue.php` config file.

### Consumers

Consumers are classes that react to events fired from your aggregate roots. There's two types of consumers: projections and process managers. Projections update read models (think updating data in databases, updating reports,...) while process managers handle one-time tasks (think sending emails, triggering builds, ...). For more information on how to use them, check out EventSauce's [Reacting to Events](https://eventsauce.io/docs/reacting-to-events/setup-consumers/) documentation.

A `SendEmailConfirmation` process manager, for example, can look like this:

```php
use App\Events\UserWasRegistered;
use App\Models\User;
use App\Notifications\NewUserNotification;
use EventSauce\LaravelEventSauce\Consumer;

final class SendConfirmationNotification extends Consumer
{
    protected function handleUserWasRegistered(UserWasRegistered $event): void
    {
        User::where('email', $event->email())
            ->first()
            ->notify(new NewUserNotification());
    }
}
```

Within this consumer you always define methods following the `handle{EventName}` specification.

#### Registering Consumers

After writing your consumer, you can register them with the `$consumers` property on the related `AggregateRootRepository`:

```php
use App\Domain\SendConfirmationNotification;
use EventSauce\LaravelEventSauce\AggregateRootRepository;

final class RegistrationAggregateRootRepository extends AggregateRootRepository
{
    protected array $consumers = [
        SendConfirmationNotification::class,
    ];
}
```

The sequence of adding consumers shouldn't matter as the data handling within these consumers should always be treated as independent from each other.

#### Queueing Consumers

By default, consumers are handled synchronous. To queue a consumer you should implement the `ShouldQueue` contract on your consumer class.

```php
use EventSauce\LaravelEventSauce\Consumer;
use Illuminate\Contracts\Queue\ShouldQueue;

final class SendConfirmationNotification extends Consumer implements ShouldQueue
{
    ...
}
```

By doing so, we'll instruct Laravel to queue the consumer and let the data handling be done at a later point in time. This is useful to delay long-running data processing.

## Changelog

Check out the [CHANGELOG](CHANGELOG.md) in this repository for all the recent changes.

## Maintainers

Laravel EventSauce is developed and maintained by [Dries Vints](https://driesvints.com).

## Acknowledgments

Thanks to [Frank De Jonge](https://twitter.com/frankdejonge) for building [EventSauce](https://eventsauce.io). Thanks to [Freek Van der Herten](https://twitter.com/freekmurze) and [Spatie's Laravel EventSauce library](https://github.com/spatie/laravel-eventsauce) for inspiration to some of the features in this package.

## License

Laravel EventSauce is open-sourced software licensed under [the MIT license](LICENSE.md).
