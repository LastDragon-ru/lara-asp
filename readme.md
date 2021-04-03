# Awesome Set of Packages for Laravel

The set provides best practices to make development more fun and classes/services that I found very useful while working on big extensible applications.

# Requirements

| Package | Version  |
| ------- | -------- |
| PHP     | >= 8.0.0 |
| Laravel | ^8.22.1  |

# Installation

```shell
# Everything
composer require lastdragon-ru/lara-asp

# Specific package (where "core" the package name)
composer require lastdragon-ru/lara-asp-core
```

# Packages

## The Core

This package contains useful utilities and classes.

[Read more](packages/core/readme.md).


## GraphQL

This package provides highly powerful `@searchBy` and `@sortBy`  directives for [lighthouse-php](https://lighthouse-php.com/). The `@searchBy` directive provides basic conditions like `=`, `>`, `<`, etc, relations, `not (<condition>)`, enums, and custom operators support. All are strictly typed so you no need to use `Mixed` type anymore. The `@sortBy` is not only about standard sorting by columns but also allows use relations. 😎

[Read more](packages/graphql/readme.md).


## The Migrator

This package improves standard laravel migrations to add support for raw SQL files during migration and seeding. So you can easily use your favorite visual tool for database development like [MySQL Workbench](https://www.mysql.com/products/workbench/) with Laravel 🥳

[Read more](packages/migrator/readme.md).


## Queue Helpers

This package provides additional capabilities for queued jobs and queued listeners like multilevel configuration support, job overriding (very useful for package development to provide base implementation and allow the application to extend it), easy define for cron jobs, and DI in constructor support.

[Read more](packages/queue/readme.md).


## Eloquent Helpers

This package contains useful extensions and mixins for [Eloquent](https://laravel.com/docs/8.x/eloquent).

[Read more](packages/eloquent/readme.md).


## The Formatter

This package provides a customizable wrapper around Intl formatters.

[Read more](packages/formatter/readme.md).


## Testing Helpers

This package provides various useful asserts for [PHPUnit](https://phpunit.de/) and alternative solution for HTTP tests - testing HTTP response has never been so easy! And this not only about `TestResponse` but any PSR response 😎

[Read more](packages/testing/readme.md).
