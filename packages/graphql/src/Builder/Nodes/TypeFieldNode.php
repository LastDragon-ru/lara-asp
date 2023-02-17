<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Nodes;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ObjectType;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;

class TypeFieldNode extends DefinitionNode {
    public function __construct(
        Manipulator $manipulator,
        private ObjectTypeDefinitionNode|ObjectType $typeDefinition,
        private FieldDefinitionNode|FieldDefinition $fieldDefinition,
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
        $type        = $manipulator->getNodeTypeFullName($this->getTypeDefinition());

        return "{$type} { {$field} }";
    }
    // </editor-fold>
}
