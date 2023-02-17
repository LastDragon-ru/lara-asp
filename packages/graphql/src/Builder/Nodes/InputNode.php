<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Nodes;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Type\Definition\InputObjectType;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;

class InputNode extends DefinitionNode {
    public function __construct(
        Manipulator $manipulator,
        private InputObjectTypeDefinitionNode|InputObjectType $inputDefinition,
    ) {
        parent::__construct($manipulator);
    }

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    public function getInputDefinition(): InputObjectTypeDefinitionNode|InputObjectType {
        return $this->inputDefinition;
    }
    // </editor-fold>

    // <editor-fold desc="NodeInfo">
    // =========================================================================
    public function getType(): string {
        return $this->getManipulator()->getNodeTypeName($this->getInputDefinition());
    }

    public function isNullable(): ?bool {
        return null;
    }

    public function isList(): ?bool {
        return null;
    }

    public function __toString(): string {
        return $this->getManipulator()->getNodeTypeFullName($this->getInputDefinition());
    }
    // </editor-fold>
}
