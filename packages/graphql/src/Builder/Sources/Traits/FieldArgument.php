<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Sources\Traits;

use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceFieldArgumentSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldArgumentSource;
use Override;

/**
 * @mixin InterfaceFieldArgumentSource|ObjectFieldArgumentSource
 * @internal
 */
trait FieldArgument {
    #[Override]
    public function __toString(): string {
        $manipulator = $this->getManipulator();
        $argument    = $manipulator->getName($this->getArgument());
        $field       = $manipulator->getName($this->getField());
        $type        = $manipulator->getTypeFullName($this->getObject());

        return "{$type} { {$field}({$argument}) }";
    }
}
