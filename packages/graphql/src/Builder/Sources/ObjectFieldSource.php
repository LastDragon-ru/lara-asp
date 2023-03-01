<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Sources;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Type\Definition\FieldDefinition;

/**
 * @extends TypeSource<FieldDefinitionNode|FieldDefinition, ObjectSource>
 */
class ObjectFieldSource extends TypeSource {
    // <editor-fold desc="TypeSource">
    // =========================================================================
    public function __toString(): string {
        $field = $this->getManipulator()->getNodeName($this->getType());
        $type  = $this->getSource();

        return "{$type} { {$field} }";
    }
    // </editor-fold>
}
