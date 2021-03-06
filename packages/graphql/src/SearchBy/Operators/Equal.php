<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operator;

class Equal extends Operator {
    public function getName(): string {
        return 'eq';
    }

    public function getDescription(): string {
        return 'Equal (`=`).';
    }
}
