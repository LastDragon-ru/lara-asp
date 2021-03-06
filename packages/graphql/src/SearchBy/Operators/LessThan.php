<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

class LessThan extends BaseOperator {
    public function getName(): string {
        return 'lt';
    }

    protected function getDescription(): string {
        return 'Less than (`<`).';
    }
}
