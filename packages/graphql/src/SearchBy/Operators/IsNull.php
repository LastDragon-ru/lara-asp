<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operator;

class IsNull extends Operator {
    public function getName(): string {
        return 'isnull';
    }

    public function getDescription(): string {
        return 'IS NULL (value of property not matter)';
    }

    public function getDefinition(string $type, bool $nullable): string {
        return parent::getDefinition('Boolean', true);
    }
}
