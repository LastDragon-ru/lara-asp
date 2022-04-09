<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts;

use GraphQL\Type\Schema;

interface SchemaPrinter {
    public function print(Schema $schema): PrintedSchema;

    public function getSettings(): Settings;

    public function setSettings(?Settings $settings): static;
}
