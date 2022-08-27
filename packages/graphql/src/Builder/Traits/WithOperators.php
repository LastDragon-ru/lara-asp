<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Traits;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator as OperatorContract;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeNoOperators;
use LastDragon_ru\LaraASP\GraphQL\Builder\Operators;

trait WithOperators {
    abstract protected function getOperators(): Operators;

    /**
     * @return array<OperatorContract>
     */
    protected function getTypeOperators(string $type, bool $nullable): array {
        $operators = $this->getOperators()->getOperators($type, $nullable);

        if (!$operators) {
            throw new TypeNoOperators($type);
        }

        return $operators;
    }
}
