# Awesome Set of Packages for Laravel

The set provides best practices to make development more fun and classes/services that I found very useful while working on big extensible applications.

[include:exec]: <./dev/artisan lara-asp-documentator:requirements>
[//]: # (start: 47823357854a9650b16a8dfa80d0576e7cb9e227)
[//]: # (warning: Generated automatically. Do not edit.)

# Requirements

| Requirement  | Constraint          | Supported by |
|--------------|---------------------|------------------|
|  PHP  | `^8.2` |   `HEAD ⋯ 2.0.0`   |
|  | `^8.1` |   `HEAD ⋯ 2.0.0`   |
|  | `^8.0` |   `4.5.2 ⋯ 2.0.0`   |
|  | `^8.0.0` |   `1.1.2 ⋯ 0.12.0`   |
|  | `>=8.0.0` |   `0.11.0 ⋯ 0.4.0`   |
|  | `>=7.4.0` |   `0.3.0 ⋯ 0.1.0`   |
|  Laravel  | `^10.0.0` |   `HEAD ⋯ 2.1.0`   |
|  | `^9.21.0` |  `HEAD`   |
|  | `^9.0.0` |   `5.0.0-beta.0 ⋯ 0.12.0`   |
|  | `^8.22.1` |   `3.0.0 ⋯ 0.2.0`   |
|  | `^8.0` |  `0.1.0`   |

[//]: # (end: 47823357854a9650b16a8dfa80d0576e7cb9e227)

# Installation

```shell
# Everything
composer require lastdragon-ru/lara-asp

# Specific package (where "core" the package name)
composer require lastdragon-ru/lara-asp-core
```

# Packages

[include:package-list]: ./packages
[//]: # (start: 0a9fb09f262722ffea6123f723b8453dd3aebdf8)
[//]: # (warning: Generated automatically. Do not edit.)

## Core

This package contains useful utilities and classes.

[Read more](<packages/core/README.md>).

## Documentator

This package provides various utilities for documentation generation.

[Read more](<packages/documentator/README.md>).

## Eloquent Helpers

This package contains useful extensions and mixins for [Eloquent](https://laravel.com/docs/eloquent).

[Read more](<packages/eloquent/README.md>).

## Formatter

This package provides a customizable wrapper around Intl formatters.

[Read more](<packages/formatter/README.md>).

## GraphQL Extensions for Lighthouse

This package provides highly powerful `@searchBy` and `@sortBy`  directives for [lighthouse-php](https://lighthouse-php.com/). The `@searchBy` directive provides basic conditions like `=`, `&gt;`, `&lt;`, etc, relations, `not (&lt;condition&gt;)`, enums, and custom operators support. All are strictly typed so you no need to use `Mixed` type anymore. The `@sortBy` is not only about standard sorting by columns but also allows use relations. 😎

[Read more](<packages/graphql/README.md>).

## GraphQL Printer

Independent (from Laravel and Lighthouse) package that allow you to print GraphQL Schema and Queries in highly customized way eg you can choose indent size, print only used/wanted/all types, print only one type, print used/wanted/all directives ([it is not possible with standard printer](https://github.com/webonyx/graphql-php/issues/552)) and even check which types/directives are used in the Schema/Query.

[Read more](<packages/graphql-printer/README.md>).

## Migrator

This package improves standard laravel migrations to add support for raw SQL files during migration and seeding. So you can easily use your favorite visual tool for database development like [MySQL Workbench](https://www.mysql.com/products/workbench/) with Laravel 🥳

[Read more](<packages/migrator/README.md>).

## Queue Helpers

This package provides additional capabilities for queued jobs and queued listeners like multilevel configuration support, job overriding (very useful for package development to provide base implementation and allow the application to extend it), easy define for cron jobs, and DI in constructor support.

[Read more](<packages/queue/README.md>).

## SPA Helpers

[Read more](<packages/spa/README.md>).

## Serializer

This package provides a customizable wrapper around the [Symfony Serializer Component](https://symfony.com/doc/current/components/serializer.html).

[Read more](<packages/serializer/README.md>).

## Testing Helpers

This package provides various useful asserts for [PHPUnit](https://phpunit.de/) and alternative solution for HTTP tests - testing HTTP response has never been so easy! And this not only about `TestResponse` but any PSR response 😎

[Read more](<packages/testing/README.md>).

[//]: # (end: 0a9fb09f262722ffea6123f723b8453dd3aebdf8)
