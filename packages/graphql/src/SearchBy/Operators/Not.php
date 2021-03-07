<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\SearchBy\Manipulator;

class Not extends BaseOperator {
    public function getName(): string {
        return 'not';
    }

    protected function getDescription(): string {
        return 'Negation.';
    }

    /**
     * @inheritdoc
     */
    public function getDefinition(array $map, string $scalar, bool $nullable): string {
        return parent::getDefinition($map, $map[Manipulator::TYPE_FLAG], true);
    }
}
