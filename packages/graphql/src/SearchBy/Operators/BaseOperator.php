<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operator;

abstract class BaseOperator implements Operator {
    abstract protected function getDescription(): string;

    public function getDefinition(string $type, bool $nullable): string {
        return <<<DEF
        """
        {$this->getDescription()}
        """
        {$this->getName()}: {$type}

        DEF;
    }
}
