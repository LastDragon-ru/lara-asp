<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Nodes;

use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Type\Definition\ObjectType;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;

class TypeNode extends DefinitionNode {
    public function __construct(
        Manipulator $manipulator,
        private ObjectTypeDefinitionNode|ObjectType $typeDefinition,
    ) {
        parent::__construct($manipulator);
    }

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    public function getTypeDefinition(): ObjectTypeDefinitionNode|ObjectType {
        return $this->typeDefinition;
    }
    // </editor-fold>

    // <editor-fold desc="NodeInfo">
    // =========================================================================
    public function getType(): string {
        return $this->getManipulator()->getNodeTypeName($this->getTypeDefinition());
    }

    public function isNullable(): ?bool {
        return null;
    }

    public function isList(): ?bool {
        return null;
    }

    public function __toString(): string {
        return $this->getManipulator()->getNodeTypeFullName($this->getTypeDefinition());
    }
    // </editor-fold>
}
