<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator;

abstract class BaseOperator implements Operator {
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
