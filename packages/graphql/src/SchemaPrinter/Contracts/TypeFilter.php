<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts;

use GraphQL\Type\Definition\Type;

interface TypeFilter {
    public function isAllowedType(Type $type, bool $isStandard): bool;
}
