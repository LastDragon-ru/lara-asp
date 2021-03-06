<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operator;

class GreaterThan extends Operator {
    public function getName(): string {
        return 'gt';
    }

    public function getDescription(): string {
        return 'Greater than (`>`).';
    }
}
