<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Contracts;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\PrintedSchema;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\PrintedType;

interface Printer {
    public function printSchema(Schema $schema): PrintedSchema;

    public function printSchemaType(Schema $schema, Type|string $type): PrintedType;

    public function printType(Type $type): PrintedType;

    public function getLevel(): int;

    public function setLevel(int $level): static;

    public function getSettings(): Settings;

    public function setSettings(?Settings $settings): static;
}
