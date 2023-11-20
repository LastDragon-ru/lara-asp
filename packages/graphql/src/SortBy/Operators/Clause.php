<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Types\Clause as ClauseType;
use Override;

class Clause extends Property {
    #[Override]
    public static function getName(): string {
        return 'condition';
    }

    #[Override]
    public function getFieldType(TypeProvider $provider, TypeSource $source): string {
        return '['.$provider->getType(ClauseType::class, $source).'!]';
    }
}
