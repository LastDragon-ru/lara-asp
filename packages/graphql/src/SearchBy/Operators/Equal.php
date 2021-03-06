<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

class Equal extends BaseOperator {
    public function getName(): string {
        return 'eq';
    }

    public function getDescription(): string {
        return 'Equal (`=`).';
    }
}
