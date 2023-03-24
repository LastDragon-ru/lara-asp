# Awesome Set of Packages for Laravel

The set provides best practices to make development more fun and classes/services that I found very useful while working on big extensible applications.

# Requirements

| Package | Version             |
|---------|---------------------|
| PHP     | `^8.0.0`            |
| Laravel | `^9.0.0`, `^10.0.0` |

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

[Read more](packages/core/README.md).

## GraphQL

This package provides highly powerful `@searchBy` and `@sortBy`  directives for [lighthouse-php](https://lighthouse-php.com/). The `@searchBy` directive provides basic conditions like `=`, `>`, `<`, etc, relations, `not (<condition>)`, enums, and custom operators support. All are strictly typed so you no need to use `Mixed` type anymore. The `@sortBy` is not only about standard sorting by columns but also allows use relations. 😎

[Read more](packages/graphql/README.md).

## GraphQL Printer

Independent (from Laravel and Lighthouse) package that allow you to print GraphQL Schema in highly customized way eg you can choose indent size, print only used/wanted/all types, print only one type, print used/wanted/all directives ([it is not possible with standard printer](https://github.com/webonyx/graphql-php/issues/552)) and even check which types/directives are used in the Schema.

[Read more](packages/graphql-printer/README.md).

## The Migrator

This package improves standard laravel migrations to add support for raw SQL files during migration and seeding. So you can easily use your favorite visual tool for database development like [MySQL Workbench](https://www.mysql.com/products/workbench/) with Laravel 🥳

[Read more](packages/migrator/README.md).

## Queue Helpers

This package provides additional capabilities for queued jobs and queued listeners like multilevel configuration support, job overriding (very useful for package development to provide base implementation and allow the application to extend it), easy define for cron jobs, and DI in constructor support.

[Read more](packages/queue/README.md).

## Eloquent Helpers

This package contains useful extensions and mixins for [Eloquent](https://laravel.com/docs/eloquent).

[Read more](packages/eloquent/README.md).

## The Formatter

This package provides a customizable wrapper around Intl formatters.

[Read more](packages/formatter/README.md).

## Testing Helpers

This package provides various useful asserts for [PHPUnit](https://phpunit.de/) and alternative solution for HTTP tests - testing HTTP response has never been so easy! And this not only about `TestResponse` but any PSR response 😎

[Read more](packages/testing/README.md).
