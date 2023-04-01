<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Sources\Traits;

use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceFieldArgumentSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldArgumentSource;

/**
 * @mixin InterfaceFieldArgumentSource|ObjectFieldArgumentSource
 * @internal
 */
trait FieldArgument {
    public function __toString(): string {
        $manipulator = $this->getManipulator();
        $argument    = $manipulator->getNodeName($this->getArgument());
        $field       = $manipulator->getNodeName($this->getField());
        $type        = $manipulator->getNodeTypeFullName($this->getObject());

        return "{$type} { {$field}({$argument}) }";
    }
}
