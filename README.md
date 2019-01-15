# LaravelEventSaucePHP

<img src="https://eventsauce.io/static/logo.svg" height="150px" width="150px">

[![Build Status](https://travis-ci.org/EventSaucePHP/EventSauceLaravel.svg?branch=master)](https://travis-ci.org/EventSaucePHP/EventSauce)

EventSauce is a somewhat opinionated, no-nonsense, and easy way to introduce event sourcing into PHP projects. By default persistence, async messaging, ... is not included in the package.

This package allows EventSauce to make use Laravel's persistence features, queues and running commands.

If you want to use EventSauce in a Laravel app, this package is the way to go!

## Installation

You can install the package via composer:

```bash
composer require eventsauce/laravel-eventsauce
```

This package comes with a migration to store all events. You can publish the migration file using:

```bash
php artisan vendor:publish --provider="EventSauce\LaravelEventSauce\EventSauceServiceProvider" --tag="migrations"
```

To create the `domain_messages` table, run the migrations

```bash
php artisan migrate
```

Next you must publish the `eventsauce` config file.

```php
php artisan vendor:publish --provider="EventSauce\LaravelEventSauce\EventSauceServiceProvider" --tag="config"
```

This is the contents of the file that will be publish to `config/eventsauce.php`

```php
//TODO: add content of config file
```

## Usage

### Code generation

We can generate types, events and commands from you starting from a yaml file. You can read more on the contents of the yaml file and the generated output in the "[Defining command and events using Yaml](https://eventsauce.io/docs/getting-started/create-events-and-commands/)" section of the EventSauce docs.

To generate code, fill in the keys in the `code_generation` parts of the `eventsauce` config file and execute this command

```
php artisan eventsauce:generate-code
```

### Testing

``` bash
./run-tests.sh
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email dries.vints@gmail.com or freek@spatie.be instead of using the issue tracker.

## Credits

- [Dries Vints](https://github.com/driesvints)
- [Freek Van der Herten](https://github.com/freekmurze)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
