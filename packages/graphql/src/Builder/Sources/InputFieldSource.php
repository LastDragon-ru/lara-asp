<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Sources;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Type\Definition\InputObjectField;

/**
 * @extends TypeSource<InputObjectTypeDefinitionNode|InputObjectField, InputNodeSource>
 */
class InputFieldSource extends TypeSource {
    // <editor-fold desc="TypeSource">
    // =========================================================================
    public function __toString(): string {
        $field = $this->getManipulator()->getNodeName($this->getType());
        $input = $this->getSource();

        return "{$input} { {$field} }";
    }
    // </editor-fold>
}
