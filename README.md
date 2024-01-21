# Awesome Set of Packages for Laravel

The set provides best practices to make development more fun and classes/services that I found very useful while working on big extensible applications.

[include:exec]: <./dev/artisan lara-asp-documentator:requirements>
[//]: # (start: 3d045d7a4689231a18ef4436deb0b7c931a93959113b45d9f544bd2b0edcf45d)
[//]: # (warning: Generated automatically. Do not edit.)

# Requirements

| Requirement  | Constraint          | Supported by |
|--------------|---------------------|------------------|
|  PHP  | `^8.3` |   `HEAD ⋯ 5.0.0`   |
|  | `^8.2` |   `HEAD ⋯ 2.0.0`   |
|  | `^8.1` |   `HEAD ⋯ 2.0.0`   |
|  | `^8.0` |   `4.6.0 ⋯ 2.0.0`   |
|  | `^8.0.0` |   `1.1.2 ⋯ 0.12.0`   |
|  | `>=8.0.0` |   `0.11.0 ⋯ 0.4.0`   |
|  | `>=7.4.0` |   `0.3.0 ⋯ 0.1.0`   |
|  Laravel  | `^10.0.0` |   `HEAD ⋯ 2.1.0`   |
|  | `^9.21.0` |   `5.4.0 ⋯ 5.0.0-beta.1`   |
|  | `^9.0.0` |   `5.0.0-beta.0 ⋯ 0.12.0`   |
|  | `^8.22.1` |   `3.0.0 ⋯ 0.2.0`   |
|  | `^8.0` |  `0.1.0`   |

[//]: # (end: 3d045d7a4689231a18ef4436deb0b7c931a93959113b45d9f544bd2b0edcf45d)

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
[//]: # (start: df3ee6374fabefbdeb79b26164b3f2ef88f6ed94646bb5d44751ea6da758de19)
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

This package provides a customizable wrapper around [Intl](https://www.php.net/manual/en/book.intl) formatters.

[Read more](<packages/formatter/README.md>).

## GraphQL Extensions for Lighthouse

This package provides highly powerful `@searchBy` and `@sortBy`  directives for [lighthouse-php](https://lighthouse-php.com/). The `@searchBy` directive provides basic conditions like `=`, `>`, `<`, etc, relations, `not (<condition>)`, enums, and custom operators support. All are strictly typed so you no need to use `Mixed` type anymore. The `@sortBy` is not only about standard sorting by columns but also allows use relations. 😎

[Read more](<packages/graphql/README.md>).

## GraphQL Printer

Independent (from Laravel and Lighthouse) package that allow you to print GraphQL Schema and Queries in highly customized way eg you can choose indent size, print only used/wanted/all types, print only one type, print used/wanted/all directives ([it is not possible with standard printer](https://github.com/webonyx/graphql-php/issues/552)) and even check which types/directives are used in the Schema/Query.

[Read more](<packages/graphql-printer/README.md>).

## Migrator

This package improves standard laravel migrations to add support for raw SQL files during migration and seeding. So you can easily use your favorite visual tool for database development like [MySQL Workbench](https://www.mysql.com/products/workbench/) with Laravel 🥳

[Read more](<packages/migrator/README.md>).

## SPA Helpers

[Read more](<packages/spa/README.md>).

## Serializer

This package provides a customizable wrapper around the [Symfony Serializer Component](https://symfony.com/doc/current/components/serializer.html).

[Read more](<packages/serializer/README.md>).

## Testing Helpers 🐝

This package provides various useful asserts for [PHPUnit](https://phpunit.de/) and alternative solution for HTTP tests - testing HTTP response has never been so easy! And this not only about `TestResponse` but any PSR response 😎

[Read more](<packages/testing/README.md>).

[//]: # (end: df3ee6374fabefbdeb79b26164b3f2ef88f6ed94646bb5d44751ea6da758de19)

[include:file]: ./docs/Shared/Upgrading.md
[//]: # (start: 58c515c01daf29a92b704a09f78da2fa719462cc37e47d3abde0331a7b1da0a3)
[//]: # (warning: Generated automatically. Do not edit.)

# Upgrading

Please follow [Upgrade Guide](UPGRADE.md).

[//]: # (end: 58c515c01daf29a92b704a09f78da2fa719462cc37e47d3abde0331a7b1da0a3)

[include:file]: ./docs/Legend.md
[//]: # (start: a974316bcb5b40e0fcedb0c38b2c3c43c80c2cadfbf95a8caf27d6f163abff0d)
[//]: # (warning: Generated automatically. Do not edit.)

# Legend

| Icon | Place | Description                                                      |
|:----:|:------|------------------------------------------------------------------|
|  🧪  | Docs  | Experimental feature. Any feedback would be greatly appreciated. |
|  🐝  | Docs  | Package intended to use in dev.                                  |
|  🤝  | Docs  | Backward-compatible change.                                      |
|  🡹  | CI    | The highest versions of dependencies are used.                   |
|  🔒  | CI    | The locked versions of dependencies are used.                    |
|  🡻  | CI    | The lowest versions of dependencies are used.                    |
|  🪓  | CI    | The optional dependencies are removed.                           |
|  🆄  | CI    | Running on Ubuntu                                                |
|  🆆  | CI    | Running on Windows                                               |
|  🅼  | CI    | Running on Mac OS X                                              |

[//]: # (end: a974316bcb5b40e0fcedb0c38b2c3c43c80c2cadfbf95a8caf27d6f163abff0d)
