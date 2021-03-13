<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operator;

abstract class BaseOperator implements Operator {
    public function getPrecedence(): int {
        return static::PrecedenceNormal;
    }

    /**
     * @inheritdoc
     */
    public function getDefinition(array $map, string $scalar, bool $nullable): string {
        return <<<DEF
        """
        {$this->getDescription()}
        """
        {$this->getName()}: {$scalar}

        DEF;
    }
}
