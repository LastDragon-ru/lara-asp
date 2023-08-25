# The Migrator

> This package is the part of Awesome Set of Packages for Laravel.
>
> [Read more](https://github.com/LastDragon-ru/lara-asp).

This package improves standard laravel migrations to add support for raw SQL files during migration and seeding. So you can easily use your favorite visual tool for database development like [MySQL Workbench](https://www.mysql.com/products/workbench/) with Laravel 🥳

| :warning: | The Migrator uses the same mechanism as [Squashing Migrations](https://laravel.com/docs/migrations#squashing-migrations) so not all databases are supported, please see Laravel Documentation for more details. |
|:---------:|:---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|

# Installation

```shell
composer require lastdragon-ru/lara-asp-migrator
```

# Migrations

To create migration just use the following [command](./docs/commands/raw-migration.md):

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

To create raw seeder just use the following [command](./docs/commands/raw-seeder.md):

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
