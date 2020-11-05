# Awesome Set of Packages for Laravel

## The Migrator

This package extends standard laravel migrations to support raw SQL files during migration and seeding. So you can easily use your favorite visual tool for database development like [MySQL Workbench](https://www.mysql.com/products/workbench/) with Laravel 🥳 

### Important note

The Migrator uses the same mechanism as [Squashing Migrations](https://laravel.com/docs/8.x/migrations#squashing-migrations) so not all databases are supported, please see Laravel Documentation for more details.


### Installation

1. Run `composer install lastdragon-ru/lara-asp-migrator`
1. Remove `DatabaseSeeder` class (`database/seeders/DatabaseSeeder.php`/`database/seeds/DatabaseSeeder.php`). The seeding will not work if this class exists.

### Migrations

To create migration just use the standard command

```
php artisan make:migration MyMigration
```

It will create the following files in `database/migrations`:
```
2020_11_05_170802_my_migration.php
2020_11_05_170802_my_migration~down.sql
2020_11_05_170802_my_migration~up.sql
```

Usually, you just need to put your SQL into `~up.sql` and `~down.sql` 😇 Note that you still can use standard Laravel migrations, but you should create it by hand. Also, migrations can be placed into subdirectories.


### Seeders

The Migrator uses a bit different approach compared to standard:

- it loads all files automatically, so you no need to call `$this->call([UserSeeder::class])` inside seeder;
- it will skip the seeder if it was already applied (it is now safe for production 🤩).

To create seeder just use standard command

```
php artisan make:seeder MySeeder
```


It will create the following files in `database/seeders` (or `database/seeds/`):

```
MySeeder.php
MySeeder.sql
```


You should place your SQL into `*.sql` and then update the model class in the `.php` file:

```
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
