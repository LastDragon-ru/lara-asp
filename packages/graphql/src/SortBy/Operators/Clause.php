<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\Traits\DirectiveName;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Types\Clause as ClauseType;

class Clause extends Property {
    use DirectiveName;

    public static function getName(): string {
        return 'Condition';
    }

    public function getFieldType(TypeProvider $provider, string $type, ?bool $nullable): string {
        return '['.$provider->getType(ClauseType::class, $type, $nullable).'!]';
    }
}
