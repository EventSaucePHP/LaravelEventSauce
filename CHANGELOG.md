# Changelog

This changelog follows [the Keep a Changelog standard](https://keepachangelog.com).

## [Unreleased](https://github.com/EventSaucePHP/LaravelEventSauce/compare/0.4.0...main)

## [0.5.0 (2021-12-19)](https://github.com/EventSaucePHP/LaravelEventSauce/compare/0.5.0...0.4.0)

### Added

- Added eventsauce 1.x support ([#31](https://github.com/EventSaucePHP/LaravelEventSauce/pull/31))

## [0.4.0 (2021-12-12)](https://github.com/EventSaucePHP/LaravelEventSauce/compare/0.3.1...0.4.0)

### Added

- Added decorator support ([bfd70ec](https://github.com/EventSaucePHP/LaravelEventSauce/commit/bfd70ecae99cfded6a0cd4398f4be2e779c92a05))

## [0.3.1 (2021-03-13)](https://github.com/EventSaucePHP/LaravelEventSauce/compare/0.3.0...0.3.1)

### Added
- Optional queue connection configuration ([#23](https://github.com/EventSaucePHP/LaravelEventSauce/pull/23))


## [0.3.0 (2020-11-01)](https://github.com/EventSaucePHP/LaravelEventSauce/compare/0.2.2...0.3.0)

### Added
- PHP 8 Support ([24a7ff3](https://github.com/EventSaucePHP/LaravelEventSauce/commit/24a7ff3fffbbc7c7afe637d0bb002a5960703b12))

### Removed
- Drop Laravel 7 support ([85aa006](https://github.com/EventSaucePHP/LaravelEventSauce/commit/85aa006267d36ebe9f93449b1ccade87eeaff1a5))


## [0.2.2 (2020-07-22)](https://github.com/EventSaucePHP/LaravelEventSauce/compare/0.2.1...0.2.2)

### Fixed
- `ramsey/uuid` v4 support ([#18](https://github.com/EventSaucePHP/LaravelEventSauce/pull/18))


## [0.2.1 (2020-03-12)](https://github.com/EventSaucePHP/LaravelEventSauce/compare/0.2.0...0.2.1)

### Fixed
- Fix aggregate root version ([#15](https://github.com/EventSaucePHP/LaravelEventSauce/pull/15))


## [0.2.0 (2020-03-11)](https://github.com/EventSaucePHP/LaravelEventSauce/compare/0.1.0...0.2.0)

### Changed
- Laravel 7 is now the minimum supported version
- PHP 7.4 is now the minimum supported version
- Aggregate root id cannot be null ([37eec03](https://github.com/EventSaucePHP/LaravelEventSauce/commit/37eec039172ce12a6875ca099d6ea0ff080a0c73))
- Rename `aggregate_root_id` column to `event_stream` ([f5682b3](https://github.com/EventSaucePHP/LaravelEventSauce/commit/f5682b39b23f4857512273b81561c381c91570d8))

### Fixed
- Protect from non-aggregate root ([89d5a7b](https://github.com/EventSaucePHP/LaravelEventSauce/commit/89d5a7b63ec46b04e7535f199bca645cc6b09352))


## 0.1.0 (2019-08-29)

Initial release.
