<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Nodes;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\NodeInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;

abstract class DefinitionNode implements NodeInfo {
    public function __construct(
        private Manipulator $manipulator,
    ) {
        // empty
    }

    protected function getManipulator(): Manipulator {
        return $this->manipulator;
    }
}
