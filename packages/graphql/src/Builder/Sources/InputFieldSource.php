<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Sources;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;

/**
 * @extends Source<(Node&TypeNode)|Type>
 */
class InputFieldSource extends Source {
    public function __construct(
        Manipulator $manipulator,
        private InputObjectTypeDefinitionNode|InputObjectType $input,
        private InputValueDefinitionNode|FieldDefinition $field,
    ) {
        parent::__construct($manipulator, $field instanceof FieldDefinition ? $field->getType() : $field->type);
    }

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    public function getInput(): InputObjectTypeDefinitionNode|InputObjectType {
        return $this->input;
    }

    public function getField(): InputValueDefinitionNode|FieldDefinition {
        return $this->field;
    }
    // </editor-fold>

    // <editor-fold desc="TypeSource">
    // =========================================================================
    public function __toString(): string {
        $manipulator = $this->getManipulator();
        $field       = $manipulator->getNodeName($this->getField());
        $type        = $manipulator->getNodeTypeFullName($this->getInput());

        return "{$type} { {$field} }";
    }
    // </editor-fold>
}
