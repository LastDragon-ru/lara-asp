<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

class NotEqual extends BaseOperator {
    public function getName(): string {
        return 'neq';
    }

    protected function getDescription(): string {
        return 'Not Equal (`!=`).';
    }
}
