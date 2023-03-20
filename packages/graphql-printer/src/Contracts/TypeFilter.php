<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Contracts;

interface TypeFilter {
    public function isAllowedType(string $type, bool $isStandard): bool;
}
