<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

interface SchemaTypePrinter {
    public function print(Schema $schema, Type|string $type): PrintedType;

    public function getLevel(): int;

    public function setLevel(int $level): static;

    public function getSettings(): Settings;

    public function setSettings(?Settings $settings): static;
}
