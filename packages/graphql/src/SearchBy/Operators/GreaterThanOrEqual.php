<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operator;

class GreaterThanOrEqual extends Operator {
    public function getName(): string {
        return 'gte';
    }

    public function getDescription(): string {
        return 'Greater than or equal to (`>=`).';
    }
}
