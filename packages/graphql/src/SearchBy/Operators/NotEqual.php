<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operator;

class NotEqual extends Operator {
    public function getName(): string {
        return 'neq';
    }

    public function getDescription(): string {
        return 'Not Equal (`!=`).';
    }
}
