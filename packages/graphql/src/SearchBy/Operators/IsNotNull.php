<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

class IsNotNull extends IsNull {
    public function getName(): string {
        return 'isNotNull';
    }

    protected function getDescription(): string {
        return 'IS NOT NULL (value of property not matter)';
    }
}
