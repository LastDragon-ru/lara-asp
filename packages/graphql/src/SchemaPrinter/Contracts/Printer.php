<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts;

use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\PrintedSchema;

interface Printer {
    public function print(Schema $schema): PrintedSchema;
}
