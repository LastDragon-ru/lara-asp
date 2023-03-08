<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Sources;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;

/**
 * @extends Source<(Node&TypeNode)|Type>
 */
class InputFieldSource extends Source {
    public function __construct(
        Manipulator $manipulator,
        private InputObjectTypeDefinitionNode|InputObjectType $object,
        private InputValueDefinitionNode|InputObjectField $field,
    ) {
        parent::__construct($manipulator, $field instanceof InputObjectField ? $field->getType() : $field->type);
    }

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    public function getObject(): InputObjectTypeDefinitionNode|InputObjectType {
        return $this->object;
    }

    public function getField(): InputValueDefinitionNode|InputObjectField {
        return $this->field;
    }
    // </editor-fold>

    // <editor-fold desc="TypeSource">
    // =========================================================================
    public function __toString(): string {
        $manipulator = $this->getManipulator();
        $field       = $manipulator->getNodeName($this->getField());
        $type        = $manipulator->getNodeTypeFullName($this->getObject());

        return "{$type} { {$field} }";
    }
    // </editor-fold>
}
