<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Nodes;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;

class InputFieldNode extends DefinitionNode {
    public function __construct(
        Manipulator $manipulator,
        private InputObjectTypeDefinitionNode|InputObjectField $inputDefinition,
        private InputValueDefinitionNode|FieldDefinition $fieldDefinition,
    ) {
        parent::__construct($manipulator);
    }

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    public function getInputDefinition(): InputObjectTypeDefinitionNode|InputObjectField {
        return $this->inputDefinition;
    }

    public function getFieldDefinition(): InputValueDefinitionNode|FieldDefinition {
        return $this->fieldDefinition;
    }
    // </editor-fold>

    // <editor-fold desc="NodeInfo">
    // =========================================================================
    public function getType(): string {
        return $this->getManipulator()->getNodeTypeName($this->getFieldDefinition());
    }

    public function isNullable(): ?bool {
        return $this->getManipulator()->isNullable($this->getFieldDefinition());
    }

    public function isList(): ?bool {
        return $this->getManipulator()->isList($this->getFieldDefinition());
    }

    public function __toString(): string {
        $manipulator = $this->getManipulator();
        $field       = $manipulator->getNodeName($this->getFieldDefinition());
        $input       = $manipulator->getNodeTypeFullName($this->getInputDefinition());

        return "{$input} { {$field} }";
    }
    // </editor-fold>
}
