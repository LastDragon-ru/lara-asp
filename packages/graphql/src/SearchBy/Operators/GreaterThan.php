<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

class GreaterThan extends BaseOperator {
    public function getName(): string {
        return 'gt';
    }

    protected function getDescription(): string {
        return 'Greater than (`>`).';
    }
}
