<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operator;

class LessThanOrEqual extends Operator {
    public function getName(): string {
        return 'lte';
    }

    public function getDescription(): string {
        return 'Less than or equal to (`<=`).';
    }
}
