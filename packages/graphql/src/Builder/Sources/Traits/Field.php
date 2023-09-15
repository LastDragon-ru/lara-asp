<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Sources\Traits;

use GraphQL\Language\AST\FieldDefinitionNode;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InputFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldSource;

use function count;

/**
 * @mixin InputFieldSource|InterfaceFieldSource|ObjectFieldSource
 * @internal
 */
trait Field {
    public function getName(): string {
        return $this->getManipulator()->getName($this->getField());
    }

    public function hasArguments(): bool {
        $node = $this->getField();
        $args = false;

        if ($node instanceof FieldDefinitionNode) {
            $args = $node->arguments->count() > 0;
        } else {
            $args = count($node->args) > 0;
        }

        return $args;
    }

    public function __toString(): string {
        $field = $this->getName();
        $type  = $this->getManipulator()->getTypeFullName($this->getObject());

        return "{$type} { {$field} }";
    }
}
