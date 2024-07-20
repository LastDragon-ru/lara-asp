# Awesome Set of Packages for Laravel

The set provides best practices to make development more fun and classes/services that I found very useful while working on big extensible applications.

[include:artisan]: <lara-asp-documentator:requirements "{$directory}">
[//]: # (start: 0f999169cbabc32d4f47c79c31d74f8b4066c685962719bae5df3c63a08ea382)
[//]: # (warning: Generated automatically. Do not edit.)

# Requirements

| Requirement  | Constraint          | Supported by |
|--------------|---------------------|------------------|
|  PHP  | `^8.3` |   `HEAD ⋯ 5.0.0`   |
|  | `^8.2` |   `HEAD ⋯ 2.0.0`   |
|  | `^8.1` |   `6.4.1 ⋯ 2.0.0`   |
|  | `^8.0` |   `4.6.0 ⋯ 2.0.0`   |
|  | `^8.0.0` |   `1.1.2 ⋯ 0.12.0`   |
|  | `>=8.0.0` |   `0.11.0 ⋯ 0.4.0`   |
|  | `>=7.4.0` |   `0.3.0 ⋯ 0.1.0`   |
|  Laravel  | `^11.0.0` |   `HEAD ⋯ 6.2.0`   |
|  | `^10.34.0` |   `HEAD ⋯ 6.2.0`   |
|  | `^10.0.0` |   `6.1.0 ⋯ 2.1.0`   |
|  | `^9.21.0` |   `5.6.0 ⋯ 5.0.0-beta.1`   |
|  | `^9.0.0` |   `5.0.0-beta.0 ⋯ 0.12.0`   |
|  | `^8.22.1` |   `3.0.0 ⋯ 0.2.0`   |
|  | `^8.0` |  `0.1.0`   |

[//]: # (end: 0f999169cbabc32d4f47c79c31d74f8b4066c685962719bae5df3c63a08ea382)

# Installation

Installation of the root `lastdragon-ru/lara-asp` package is not recommended because it will install all packages, but some of them are intended to use while dev only (and may want dependencies like `phpunit`/`phpstan`/etc). So select the desired package and install it. You can find requirements and installation instructions (if any) inside package documentation.

```shell
# General case (where "<package>" the package name).
composer require lastdragon-ru/lara-asp-<package>
```

# Packages

| 🐝 | Package intended to use in dev. |
|:--:|---------------------------------|

[include:package-list]: ./packages
[//]: # (start: fed4ec353a970d6253d640feee67d7662b2a28757689a2bd7276fe1136209b8a)
[//]: # (warning: Generated automatically. Do not edit.)

## (Laravel) Core

This package contains useful utilities and classes.

[Read more](<packages/core/README.md>).

## (Laravel) Documentator

This package provides various utilities for documentation generation such as Markdown Preprocessor, Requirements Dumper and more.

[Read more](<packages/documentator/README.md>).

## (Laravel) Eloquent Helpers

This package contains useful extensions and mixins for [Eloquent](https://laravel.com/docs/eloquent).

[Read more](<packages/eloquent/README.md>).

## (Laravel) GraphQL Extensions for Lighthouse

This package provides highly powerful `@searchBy`, `@sortBy`, `@stream` directives for [lighthouse-php](https://lighthouse-php.com/). The `@searchBy` directive provides basic conditions like `=`, `>`, `<`, etc, relations, `not (<condition>)`, enums, and custom operators support. All are strictly typed so you no need to use `Mixed` type anymore. The `@sortBy` is not only about standard sorting by columns but also allows use relations. 😎

[Read more](<packages/graphql/README.md>).

## (Laravel) Intl Formatter

This package provides a customizable wrapper around [Intl](https://www.php.net/manual/en/book.intl) formatters to use it inside Laravel application.

[Read more](<packages/formatter/README.md>).

## (Laravel) Raw SQL Migrator/Seeder

This package improves standard Laravel migrations to add support for raw SQL files during migration and seeding. So you can easily use your favorite visual tool for database development like [MySQL Workbench](https://www.mysql.com/products/workbench/) with Laravel 🥳

[Read more](<packages/migrator/README.md>).

## (Laravel) SPA Helpers

[Read more](<packages/spa/README.md>).

## (Laravel) Symfony Serializer

This package provides a customizable wrapper around the [Symfony Serializer Component](https://symfony.com/doc/current/components/serializer.html) to use it inside Laravel application.

[Read more](<packages/serializer/README.md>).

## (Laravel) Testing Helpers 🐝

This package provides various useful asserts for [PHPUnit](https://phpunit.de/) and better solution for HTTP tests - testing HTTP response has never been so easy! And this not only about `TestResponse` but any PSR response 😎

[Read more](<packages/testing/README.md>).

## Dev 🐝

Various internal tools and helpers to develop the package itself.

[Read more](<packages/dev/README.md>).

## GraphQL Printer

Independent (from Laravel and Lighthouse) package that allow you to print GraphQL Schema and Queries in highly customized way eg you can choose indent size, print only used/wanted/all types, print only one type, print used/wanted/all directives ([it is not possible with standard printer](https://github.com/webonyx/graphql-php/issues/552)) and even check which types/directives are used in the Schema/Query.

[Read more](<packages/graphql-printer/README.md>).

[//]: # (end: fed4ec353a970d6253d640feee67d7662b2a28757689a2bd7276fe1136209b8a)

[include:file]: ./docs/Shared/Upgrading.md
[//]: # (start: c18391a9383a4b80e3507a660c7927062227f2c3f8d7a34ba11721a5cc9c5294)
[//]: # (warning: Generated automatically. Do not edit.)

# Upgrading

Please follow [Upgrade Guide](UPGRADE.md).

[//]: # (end: c18391a9383a4b80e3507a660c7927062227f2c3f8d7a34ba11721a5cc9c5294)

# Development

1. Fork & clone
2. `docker compose up`
3. ...
4. Enjoy

[include:file]: ./docs/Legend.md
[//]: # (start: c27a3d72b85555e946885f9f19e2ece38998f1207fbd278bb3058890b864132a)
[//]: # (warning: Generated automatically. Do not edit.)

# Legend

| Icon | Place | Description                                                      |
|:----:|:------|------------------------------------------------------------------|
|  🧪  | Docs  | Experimental feature. Any feedback would be greatly appreciated. |
|  🐝  | Docs  | Package intended to use in dev.                                  |
|  🤝  | Docs  | Backward-compatible change.                                      |
|  💀  | Docs  | Deprecated feature.                                              |
|  🡹  | CI    | The highest versions of dependencies are used.                   |
|  🔒  | CI    | The locked versions of dependencies are used.                    |
|  🡻  | CI    | The lowest versions of dependencies are used.                    |
|  🪓  | CI    | The optional dependencies are removed.                           |
|  🆄  | CI    | Running on Ubuntu                                                |
|  🆆  | CI    | Running on Windows                                               |
|  🅼  | CI    | Running on Mac OS X                                              |

[//]: # (end: c27a3d72b85555e946885f9f19e2ece38998f1207fbd278bb3058890b864132a)
