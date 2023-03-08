<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Traits\DirectiveName;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Condition as ConditionType;

class Condition extends Property {
    use DirectiveName;

    public static function getName(): string {
        return 'Condition';
    }

    public function getFieldType(TypeProvider $provider, TypeSource $type): string {
        return $provider->getType(ConditionType::class, $type);
    }
}
