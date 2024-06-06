# (Laravel) Raw SQL Migrator/Seeder

This package improves standard Laravel migrations to add support for raw SQL files during migration and seeding. So you can easily use your favorite visual tool for database development like [MySQL Workbench](https://www.mysql.com/products/workbench/) with Laravel 🥳

> [!IMPORTANT]
>
> The Migrator uses the same mechanism as [Squashing Migrations](https://laravel.com/docs/migrations#squashing-migrations) so not all databases are supported, please see Laravel Documentation for more details.

[include:artisan]: <lara-asp-documentator:requirements "{$directory}">
[//]: # (start: 3556073e7992c5bd81cdd63a92c38d136657c7e720caec135fff44e925557f7b)
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
|  Laravel  | `^11.0.0` |   `HEAD ⋯ 6.2.0`   |
|  | `^10.34.0` |   `HEAD ⋯ 6.2.0`   |
|  | `^10.0.0` |   `6.1.0 ⋯ 2.1.0`   |
|  | `^9.21.0` |   `5.6.0 ⋯ 5.0.0-beta.1`   |
|  | `^9.0.0` |   `5.0.0-beta.0 ⋯ 0.12.0`   |
|  | `^8.22.1` |   `3.0.0 ⋯ 0.2.0`   |
|  | `^8.0` |  `0.1.0`   |

[//]: # (end: 3556073e7992c5bd81cdd63a92c38d136657c7e720caec135fff44e925557f7b)

[include:template]: ../../docs/Shared/Installation.md ({"data": {"package": "migrator"}})
[//]: # (start: 71480f577837f2b64afb81d2af134daeb17eef704953d93e8f393d804443e2a4)
[//]: # (warning: Generated automatically. Do not edit.)

# Installation

```shell
composer require lastdragon-ru/lara-asp-migrator
```

[//]: # (end: 71480f577837f2b64afb81d2af134daeb17eef704953d93e8f393d804443e2a4)

# Migrations

To create migration, just use the following [command](docs/Commands/sql-migration.md):

```shell
php artisan lara-asp-migrator:sql-migration MyMigration
```

It will create the following files in `database/migrations`:

```text
2020_11_05_170802_my_migration.php
2020_11_05_170802_my_migration~down.sql
2020_11_05_170802_my_migration~up.sql
```

Usually, you just need to put your SQL into `*~up.sql` and `*~down.sql` 😇 Note that you still can use standard Laravel migrations. Also, migrations can be placed into subdirectories.

# Seeders

The package uses a bit different approach compared with standard and provides a few different types of seeders:

* [`Seeder`](./src/Seeders/Seeder.php) - unlike standard `Seeder` it is safer and will not run seeder if it is already applied (so it is safe for production 🤩);
* [`SqlSeeder`](./src/Seeders/SqlSeeder.php) - extends `SmartSeeder` and allow you to use SQL.

To create SQL Seeder you should use the [command](docs/Commands/sql-seeder.md):

```shell
php artisan lara-asp-migrator:sql-seeder MySeeder
```

The command will create two files:

* `MySeeder.php` - The class can be used to customize `isSeeded()` method, e.g. you can check if a model exists in the database.
* `MySeeder.sql` - the file where the SQL seed stored.

[include:file]: ../../docs/Shared/Upgrading.md
[//]: # (start: 5f4a27dda34e5e151a62fe3459daf4bb3b85705d38810060e71fcadc25669c0f)
[//]: # (warning: Generated automatically. Do not edit.)

# Upgrading

Please follow [Upgrade Guide](UPGRADE.md).

[//]: # (end: 5f4a27dda34e5e151a62fe3459daf4bb3b85705d38810060e71fcadc25669c0f)

[include:file]: ../../docs/Shared/Contributing.md
[//]: # (start: 3f7cfa48046722fb9d277c71e074ff8406787772f90d17405b7554a4464cbfee)
[//]: # (warning: Generated automatically. Do not edit.)

# Contributing

This package is the part of Awesome Set of Packages for Laravel. Please use the [main repository](https://github.com/LastDragon-ru/lara-asp) to [report issues](https://github.com/LastDragon-ru/lara-asp/issues), send [pull requests](https://github.com/LastDragon-ru/lara-asp/pulls), or [ask questions](https://github.com/LastDragon-ru/lara-asp/discussions).

[//]: # (end: 3f7cfa48046722fb9d277c71e074ff8406787772f90d17405b7554a4464cbfee)
