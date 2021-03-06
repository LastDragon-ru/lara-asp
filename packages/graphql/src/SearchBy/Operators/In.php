<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operator;

class In extends Operator {
    public function getName(): string {
        return 'in';
    }

    public function getDescription(): string {
        return 'Within a set of values.';
    }

    public function getDefinition(string $type, bool $nullable): string {
        return parent::getDefinition("[{$type}!]", true);
    }
}
