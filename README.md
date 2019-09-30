<p>
    <img src="https://eventsauce.io/static/logo.svg" height="120px" width="120px">
</p>

# Laravel EventSauce

<p>
    <a href="https://packagist.org/packages/eventsauce/laravel-eventsauce">
        <img src="https://img.shields.io/packagist/v/eventsauce/laravel-eventsauce.svg" alt="Packagist Version">
    </a>
    <a href="https://travis-ci.org/EventSaucePHP/LaravelEventSauce?branch=master">
        <img src="https://travis-ci.org/EventSaucePHP/LaravelEventSauce.svg?branch=master" alt="Travis">
    </a>
    <a href="https://packagist.org/packages/eventsauce/laravel-eventsauce">
        <img src="https://img.shields.io/packagist/dt/eventsauce/laravel-eventsauce.svg" alt="Downloads">
    </a>
</p>

This library is a work in progress. More docs coming soon...

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Acknowledgments](#acknowledgments)
- [License](#license)

## Requirements

- PHP ^7.3
- Laravel ^6.0

## Installation

You can install the library through [Composer](https://getcomposer.org). This will also install [the main EventSauce library](https://github.com/EventSaucePHP/EventSauce).

```bash
composer require eventsauce/laravel-eventsauce
```

## Configuration

### Migrations

The default `domain_messages` table will be loaded in through the library's service provider and migrated with:
 
```bash
php artisan migrate
```

You can also publish it and modify it as you see fit with the following command:

```bash
php artisan vendor:publish --tag="eventsauce-migrations"
```

### Config Options

Publish the config file with the following command:

```bash
php artisan vendor:publish --tag="eventsauce-config"
```

#### Default Connection

The default database connection can be modified by setting the `EVENTSAUCE_CONNECTION` env variable:

```dotenv
EVENTSAUCE_CONNECTION=mysql
```

#### Default Table

The default table name for your domain messages can be set with the `EVENTSAUCE_TABLE` env variable:

```dotenv
EVENTSAUCE_TABLE=event_store
```

## Generating Commands & Events

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
    \App\Domain\Registration\RegistrationAggregateRootRepository::class,
],
```

You can now generate commands and events for all repositories that you've added by running the following command:

```bash
php artisan eventsauce:generate
```

For more info on creating events and commands with EventSauce, as well as how to define different types, see: https://eventsauce.io/docs/getting-started/create-events-and-commands

## Acknowledgments

Thanks to [Frank De Jonge](https://twitter.com/frankdejonge) for building [EventSauce](https://eventsauce.io). Thanks to [Freek Van der Herten](https://twitter.com/freekmurze) and [Spatie's Laravel EventSauce library](https://github.com/spatie/laravel-eventsauce) for inspiration to some of the features in this package.

## License

The MIT License (MIT). Please see [the License File](LICENSE.md) for more information.
