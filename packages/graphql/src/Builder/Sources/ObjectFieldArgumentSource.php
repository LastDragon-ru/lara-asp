<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Sources;

use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Type\Definition\FieldArgument;

/**
 * @extends TypeSource<InputValueDefinitionNode|FieldArgument, ObjectFieldSource>
 */
class ObjectFieldArgumentSource extends TypeSource {
    // <editor-fold desc="TypeSource">
    // =========================================================================
    public function __toString(): string {
        $manipulator = $this->getManipulator();
        $argument    = $manipulator->getNodeName($this->getType());
        $field       = $manipulator->getNodeName($this->getSource()->getType());
        $type        = $this->getSource()->getSource();

        return "{$type} { {$field}({$argument}) }";
    }
    // </editor-fold>
}
