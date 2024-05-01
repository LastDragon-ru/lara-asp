# (Laravel) Raw SQL Migrator/Seeder

This package improves standard Laravel migrations to add support for raw SQL files during migration and seeding. So you can easily use your favorite visual tool for database development like [MySQL Workbench](https://www.mysql.com/products/workbench/) with Laravel 🥳

> [!IMPORTANT]
>
> The Migrator uses the same mechanism as [Squashing Migrations](https://laravel.com/docs/migrations#squashing-migrations) so not all databases are supported, please see Laravel Documentation for more details.

[include:exec]: <../../dev/artisan lara-asp-documentator:requirements>
[//]: # (start: 876a9177c0e8e3722ac84e8f3888245fc9070a64a87dedfe7c9d9ba2a13b374b)
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

[//]: # (end: 876a9177c0e8e3722ac84e8f3888245fc9070a64a87dedfe7c9d9ba2a13b374b)

[include:template]: ../../docs/Shared/Installation.md ({"data": {"package": "migrator"}})
[//]: # (start: 71480f577837f2b64afb81d2af134daeb17eef704953d93e8f393d804443e2a4)
[//]: # (warning: Generated automatically. Do not edit.)

# Installation

```shell
composer require lastdragon-ru/lara-asp-migrator
```

[//]: # (end: 71480f577837f2b64afb81d2af134daeb17eef704953d93e8f393d804443e2a4)

# Migrations

To create migration just use the following [command](docs/Commands/raw-migration.md):

```shell
php artisan lara-asp-migrator:raw-migration MyMigration
```

It will create the following files in `database/migrations`:

```text
2020_11_05_170802_my_migration.php
2020_11_05_170802_my_migration~down.sql
2020_11_05_170802_my_migration~up.sql
```

Usually, you just need to put your SQL into `~up.sql` and `~down.sql` 😇 Note that you still can use standard Laravel migrations, but you should create it manually. Also, migrations can be placed into subdirectories.

Another useful class is [`RawDataMigration`](./src/Migrations/RawDataMigration.php) that specially designed for cases when you want to insert data without altering the table(s). Unlike `RawMigration` it will apply migration only if the database is not empty (to fill empty database while fresh installation please use Seeders).

# Seeders

The Migrator uses a bit different approach compared to standard and provides a few different types of seeders:

* [`SmartSeeder`](./src/Seeders/SmartSeeder.php) - unlike standard `Seeder` it is safer and will not run seeder if it is already applied (so it is safe for production 🤩);
* [`RawSeeder`](./src/Seeders/RawSeeder.php) - extends `SmartSeeder` and allow you to use SQL.

To create raw seeder just use the following [command](docs/Commands/raw-seeder.md):

```shell
php artisan lara-asp-migrator:raw-seeder MySeeder
```

It will create the following files in `database/seeders` (or `database/seeds/`):

```text
MySeeder.php
MySeeder.sql
```

You should place your SQL into `*.sql` and then update the model class in the `.php` file:

```php
<?php declare(strict_types = 1);

namespace Database\Seeders;

use LastDragon_ru\LaraASP\Migrator\Seeders\RawSeeder;

class MySeeder extends RawSeeder {
    protected function getTarget(): ?string {
        // Base class will check that the table has any records and stop seeding
        // if it is not empty.
        return Model::class;
    }
}
```

[include:file]: ../../docs/Shared/Upgrading.md
[//]: # (start: e9139abedb89f69284102c9112b548fd7add07cf196259916ea4f1c98977223b)
[//]: # (warning: Generated automatically. Do not edit.)

# Upgrading

Please follow [Upgrade Guide](UPGRADE.md).

[//]: # (end: e9139abedb89f69284102c9112b548fd7add07cf196259916ea4f1c98977223b)

[include:file]: ../../docs/Shared/Contributing.md
[//]: # (start: 057ec3a599c54447e95d6dd2e9f0f6a6621d9eb75446a5e5e471ba9b2f414b89)
[//]: # (warning: Generated automatically. Do not edit.)

# Contributing

This package is the part of Awesome Set of Packages for Laravel. Please use the [main repository](https://github.com/LastDragon-ru/lara-asp) to [report issues](https://github.com/LastDragon-ru/lara-asp/issues), send [pull requests](https://github.com/LastDragon-ru/lara-asp/pulls), or [ask questions](https://github.com/LastDragon-ru/lara-asp/discussions).

[//]: # (end: 057ec3a599c54447e95d6dd2e9f0f6a6621d9eb75446a5e5e471ba9b2f414b89)
