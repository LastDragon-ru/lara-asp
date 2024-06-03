<?php declare(strict_types = 1);

// @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\Migrator\Migrations\SqlMigration;

return Container::getInstance()->call(
    new class extends SqlMigration {
        // empty
    },
)
    ->upFrom(null);
