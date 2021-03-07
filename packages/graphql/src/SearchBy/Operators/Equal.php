<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\SearchBy\OperatorNegationable;

class Equal extends BaseOperator implements OperatorNegationable {
    public function getName(): string {
        return 'eq';
    }

    protected function getDescription(): string {
        return 'Equal (`=`).';
    }
}
