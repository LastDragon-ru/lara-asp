<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Sources;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Type\Definition\Argument;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;

/**
 * @extends Source<NamedTypeNode|ListTypeNode|NonNullTypeNode|Type>
 */
class InterfaceFieldArgumentSource extends Source {
    public function __construct(
        Manipulator $manipulator,
        private InterfaceTypeDefinitionNode|InterfaceType $object,
        private FieldDefinitionNode|FieldDefinition $field,
        private InputValueDefinitionNode|Argument $argument,
    ) {
        parent::__construct($manipulator, $argument instanceof Argument ? $argument->getType() : $argument->type);
    }

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    public function getObject(): InterfaceTypeDefinitionNode|InterfaceType {
        return $this->object;
    }

    public function getField(): FieldDefinition|FieldDefinitionNode {
        return $this->field;
    }

    public function getArgument(): InputValueDefinitionNode|Argument {
        return $this->argument;
    }
    // </editor-fold>

    // <editor-fold desc="TypeSource">
    // =========================================================================
    public function __toString(): string {
        $manipulator = $this->getManipulator();
        $argument    = $manipulator->getNodeName($this->getArgument());
        $field       = $manipulator->getNodeName($this->getField());
        $type        = $manipulator->getNodeTypeFullName($this->getObject());

        return "{$type} { {$field}({$argument}) }";
    }
    // </editor-fold>
}
