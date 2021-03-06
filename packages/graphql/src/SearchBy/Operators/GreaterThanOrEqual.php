<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

class GreaterThanOrEqual extends BaseOperator {
    public function getName(): string {
        return 'gte';
    }

    public function getDescription(): string {
        return 'Greater than or equal to (`>=`).';
    }
}
