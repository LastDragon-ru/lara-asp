# Awesome Set of Packages for Laravel

The set provides best practices to make development more fun and classes/services that I found very useful while working on big extensible applications.

[include:artisan]: <lara-asp-documentator:requirements "{$directory}">
[//]: # (start: preprocess/78cfc4c7c7c55577)
[//]: # (warning: Generated automatically. Do not edit.)

# Requirements

| Requirement  | Constraint          | Supported by |
|--------------|---------------------|------------------|
|  PHP  | `^8.4` |   `HEAD ⋯ 8.0.0`   |
|  | `^8.3` |   `HEAD ⋯ 5.0.0`   |
|  | `^8.2` |   `7.2.0 ⋯ 2.0.0`   |
|  | `^8.1` |   `6.4.2 ⋯ 2.0.0`   |
|  | `^8.0` |   `4.6.0 ⋯ 2.0.0`   |
|  | `^8.0.0` |   `1.1.2 ⋯ 0.12.0`   |
|  | `>=8.0.0` |   `0.11.0 ⋯ 0.4.0`   |
|  | `>=7.4.0` |   `0.3.0 ⋯ 0.1.0`   |
|  Laravel  | `^12.0.1` |   `HEAD ⋯ 9.0.0`   |
|  | `^11.0.8` |   `8.1.1 ⋯ 8.0.0`   |
|  | `^11.0.0` |   `7.2.0 ⋯ 6.2.0`   |
|  | `^10.34.0` |   `7.2.0 ⋯ 6.2.0`   |
|  | `^10.0.0` |   `6.1.0 ⋯ 2.1.0`   |
|  | `^9.21.0` |   `5.6.0 ⋯ 5.0.0-beta.1`   |
|  | `^9.0.0` |   `5.0.0-beta.0 ⋯ 0.12.0`   |
|  | `^8.22.1` |   `3.0.0 ⋯ 0.2.0`   |
|  | `^8.0` |  `0.1.0`   |

[//]: # (end: preprocess/78cfc4c7c7c55577)

# Installation

Installation of the root `lastdragon-ru/lara-asp` package is not recommended because it will install all packages, but some of them are intended to use while dev only (and may want dependencies like `phpunit`/`phpstan`/etc). So select the desired package and install it. You can find requirements and installation instructions (if any) inside package documentation.

```shell
# General case (where "<package>" the package name).
composer require lastdragon-ru/lara-asp-<package>
```

# Packages

| 🐝 | Package intended to use in dev. |
|:--:|---------------------------------|

[include:document-list]: ./packages ({"include": ["*/README.md"]})
[//]: # (start: preprocess/2814e5e8c6b59bac)
[//]: # (warning: Generated automatically. Do not edit.)

## [(Laravel) Core](<packages/lara-asp-core/README.md>)

This package contains useful utilities and classes.

[Read more](<packages/lara-asp-core/README.md>).

## [(Laravel) Documentator](<packages/documentator/README.md>)

This package provides various utilities for documentation generation such as Markdown Preprocessor, Requirements Dumper and more.

[Read more](<packages/documentator/README.md>).

## [(Laravel) Eloquent Helpers](<packages/eloquent/README.md>)

This package contains useful extensions and mixins for [Eloquent](https://laravel.com/docs/eloquent).

[Read more](<packages/eloquent/README.md>).

## [(Laravel) GraphQL Extensions for Lighthouse](<packages/graphql/README.md>)

This package provides highly powerful [`@searchBy`](packages/graphql/docs/Directives/@searchBy.md), [`@sortBy`](packages/graphql/docs/Directives/@sortBy.md), [`@stream`](packages/graphql/docs/Directives/@stream.md) directives for [lighthouse-php](https://lighthouse-php.com/). The [`@searchBy`](packages/graphql/docs/Directives/@searchBy.md) directive provides basic conditions like `=`, `>`, `<`, etc, relations, `not (<condition>)`, enums, and custom operators support. All are strictly typed so you no need to use `Mixed` type anymore. The [`@sortBy`](packages/graphql/docs/Directives/@sortBy.md) is not only about standard sorting by columns but also allows use relations. 😎

[Read more](<packages/graphql/README.md>).

## [(Laravel) GraphQL Testing Assertions for Laravel/Lighthouse 🐝](<packages/graphql-testing/README.md>)

Useful assertions for PHPUnit to check printed/exported type/queries and more with [`lastdragon-ru/lara-asp-graphql`](packages/graphql/README.md) package

[Read more](<packages/graphql-testing/README.md>).

## [(Laravel) Intl Formatter](<packages/formatter/README.md>)

This package provides a customizable wrapper around [Intl](https://www.php.net/manual/en/book.intl) formatters to use it inside Laravel application. And also allows defining own.

[Read more](<packages/formatter/README.md>).

## [(Laravel) Raw SQL Migrator/Seeder](<packages/migrator/README.md>)

This package improves standard Laravel migrations to add support for raw SQL files during migration and seeding. So you can easily use your favorite visual tool for database development like [MySQL Workbench](https://www.mysql.com/products/workbench/) with Laravel 🥳

[Read more](<packages/migrator/README.md>).

## [(Laravel) SPA Helpers](<packages/spa/README.md>)

[Read more](<packages/spa/README.md>).

## [(Laravel) Symfony Serializer](<packages/serializer/README.md>)

This package provides a customizable wrapper around the [Symfony Serializer Component](https://symfony.com/doc/current/components/serializer.html) to use it inside Laravel application.

[Read more](<packages/serializer/README.md>).

## [(Laravel) Testing Helpers 🐝](<packages/testing/README.md>)

This package provides various useful asserts for [PHPUnit](https://phpunit.de/) and better solution for HTTP tests - testing HTTP response has never been so easy! And this not only about `TestResponse` but any PSR response 😎

[Read more](<packages/testing/README.md>).

## [Dev 🐝](<packages/dev/README.md>)

Various internal tools and helpers to develop the package itself.

[Read more](<packages/dev/README.md>).

## [DIY Parser](<packages/text-parser/README.md>)

There are several tools to generate full-featured parsers even for PHP. They are overkill when you just need to parse something simple. In such cases, you might decide to create your own parser. There are a lot of articles/examples on the web, and actually it is not too difficult as you may think. This is yet another package to simplify writing fast and memory-effective parsers that can parse infinite strings.

[Read more](<packages/text-parser/README.md>).

## [Glob](<packages/glob-matcher/README.md>)

Full-featured well-tested glob pattern parser and matcher: basic matching (`?`, `*`), globstar (`**`), extglob (`?(pattern-list)`, `*(pattern-list)`, `+(pattern-list)`, `@(pattern-list)`, `!(pattern-list)`), brace expansion (`{a,b,c}.txt`, `{1..3}.txt`, etc), dotglob, nocasematch, POSIX Named character classes (`[:alnum:]`, etc), POSIX Collating symbols (`[.ch.]`, etc), POSIX Equivalence class expressions (`[=a=]`, etc), and escaping. Everything supported 😎

[Read more](<packages/glob-matcher/README.md>).

## [GraphQL Printer](<packages/graphql-printer/README.md>)

Independent (from Laravel and Lighthouse) package that allow you to print GraphQL Schema and Queries in highly customized way eg you can choose indent size, print only used/wanted/all types, print only one type, print used/wanted/all directives ([it is not possible with standard printer](https://github.com/webonyx/graphql-php/issues/552)) and even check which types/directives are used in the Schema/Query.

[Read more](<packages/graphql-printer/README.md>).

## [GraphQL Testing Assertions 🐝](<packages/graphql-printer-testing/README.md>)

Useful assertions for PHPUnit to check printed/exported type/queries with [`lastdragon-ru/graphql-printer`](packages/graphql-printer/README.md) package

[Read more](<packages/graphql-printer-testing/README.md>).

## [Path](<packages/path/README.md>)

Provides utilities for working with file and directory paths in an object-oriented way for all path types.

[Read more](<packages/path/README.md>).

[//]: # (end: preprocess/2814e5e8c6b59bac)

# Upgrading

Please follow [Upgrade Guide](UPGRADE.md).

# Development

1. Fork & clone
2. `docker compose up`
3. Write the code
4. `composer run validate:commit`
5. `composer run rebuild:docs` (if docs changed)
6. Push & PR
7. ...
8. Enjoy

# Support policy

Project following to the [semver](https://semver.org/) including parameter names, but except code marked as `private`/`@internal`.

* **Lara ASP**
  * `main` branch - new features and any Breaking Changes (non BC things can be back-ported to the last stable)
  * current stable - bug fixes
  * previous stable - PRs with bug fixes will be accepted if no BC
* **PHP** - last two active versions (eg `8.3` and `8.4`)
* **Laravel** - last version
* **Lighthouse** - last version

[include:file]: ./docs/Legend.md
[//]: # (start: preprocess/5488d85d082e47fb)
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

[//]: # (end: preprocess/5488d85d082e47fb)
