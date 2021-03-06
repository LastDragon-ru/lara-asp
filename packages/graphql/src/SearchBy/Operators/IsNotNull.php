<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operator;

class IsNotNull extends Operator {
    public function getName(): string {
        return 'isNotNull';
    }

    public function getDescription(): string {
        return 'IS NOT NULL (value of property not matter)';
    }

    public function getDefinition(string $type, bool $nullable): string {
        return parent::getDefinition('Boolean', true);
    }
}
