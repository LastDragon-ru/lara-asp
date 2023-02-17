<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Nodes;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Type\Definition\FieldArgument;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ObjectType;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;

class TypeFieldArgumentNode extends DefinitionNode {
    public function __construct(
        Manipulator $manipulator,
        private ObjectTypeDefinitionNode|ObjectType $typeDefinition,
        private FieldDefinitionNode|FieldDefinition $fieldDefinition,
        private InputValueDefinitionNode|FieldArgument $argumentDefinition,
    ) {
        parent::__construct($manipulator);
    }

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    public function getTypeDefinition(): ObjectTypeDefinitionNode|ObjectType {
        return $this->typeDefinition;
    }

    public function getFieldDefinition(): FieldDefinitionNode|FieldDefinition {
        return $this->fieldDefinition;
    }

    public function getArgumentDefinition(): InputValueDefinitionNode|FieldArgument {
        return $this->argumentDefinition;
    }
    // </editor-fold>

    // <editor-fold desc="NodeInfo">
    // =========================================================================
    public function getType(): string {
        return $this->getManipulator()->getNodeTypeName($this->getArgumentDefinition());
    }

    public function isNullable(): ?bool {
        return $this->getManipulator()->isNullable($this->getArgumentDefinition());
    }

    public function isList(): ?bool {
        return $this->getManipulator()->isList($this->getArgumentDefinition());
    }

    public function __toString(): string {
        $manipulator = $this->getManipulator();
        $argument    = $manipulator->getNodeName($this->getArgumentDefinition());
        $field       = $manipulator->getNodeName($this->getFieldDefinition());
        $type        = $manipulator->getNodeTypeFullName($this->getTypeDefinition());

        return "{$type} { {$field}({$argument}) }";
    }
    // </editor-fold>
}
