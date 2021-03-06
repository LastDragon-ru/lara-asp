<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

abstract class Operator {
    abstract public function getName(): string;

    abstract public function getDescription(): string;

    public function getDefinition(string $type, bool $nullable): string {
        return <<<DEF
        """
        {$this->getDescription()}
        """
        {$this->getName()}: {$type}

        DEF;
    }
}
