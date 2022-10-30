<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Traits;

use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator as OperatorContract;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeNoOperators;
use LastDragon_ru\LaraASP\GraphQL\Builder\Operators;

use function array_filter;

trait WithOperators {
    abstract protected function getOperators(): Operators;

    abstract protected function getBuilderInfo(): BuilderInfo;

    /**
     * @return array<OperatorContract>
     */
    protected function getTypeOperators(string $type, bool $nullable): array {
        $operators = $this->getOperators()->getOperators($type, $nullable);
        $operators = array_filter($operators, function (Operator $operator): bool {
            return $operator->isBuilderSupported($this->getBuilderInfo()->getBuilder());
        });

        if (!$operators) {
            throw new TypeNoOperators($type);
        }

        return $operators;
    }
}
