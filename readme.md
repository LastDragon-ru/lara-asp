# Awesome Set of Packages for Laravel

The set contains provides best practices to make development more fun and classes/services that I found very useful while working on big extensible applications.

# Installation

```shell
# Everything
composer require lastdragon-ru/lara-asp

# Specific package (where "core" the package name)
composer require lastdragon-ru/lara-asp-core
```

# Packages

## The Migrator

This package improves standard laravel migrations to add support for raw SQL files during migration and seeding. So you can easily use your favorite visual tool for database development like [MySQL Workbench](https://www.mysql.com/products/workbench/) with Laravel 🥳

[Read more](packages/migrator/readme.md).

## Queue Helpers

This package provides additional capabilities for queued jobs and queued listeners like multilevel configuration support, job overriding (very useful for package development to provide base implementation and allow the application to extend it), easy define for cron jobs, and DI in constructor support.

[Read more](packages/queue/readme.md).
