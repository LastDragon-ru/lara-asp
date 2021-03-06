<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

class LessThanOrEqual extends BaseOperator {
    public function getName(): string {
        return 'lte';
    }

    protected function getDescription(): string {
        return 'Less than or equal to (`<=`).';
    }
}
