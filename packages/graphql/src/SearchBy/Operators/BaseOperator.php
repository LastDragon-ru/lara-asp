<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeProvider;

abstract class BaseOperator implements Operator {
    public function __construct() {
        // empty
    }

    public function getDefinition(TypeProvider $provider, string $scalar, bool $nullable): string {
        return <<<DEF
        """
        {$this->getDescription()}
        """
        {$this->getName()}: {$scalar}

        DEF;
    }

    abstract protected function getDescription(): string;
}
