<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Sources;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\FieldArgument;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;

/**
 * @extends Source<(Node&TypeNode)|Type>
 */
class ObjectFieldArgumentSource extends Source {
    public function __construct(
        Manipulator $manipulator,
        private ObjectTypeDefinitionNode|ObjectType $object,
        private FieldDefinitionNode|FieldDefinition $field,
        private InputValueDefinitionNode|FieldArgument $argument,
    ) {
        parent::__construct($manipulator, $argument instanceof FieldArgument ? $argument->getType() : $argument->type);
    }

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    public function getObject(): ObjectTypeDefinitionNode|ObjectType {
        return $this->object;
    }

    public function getField(): FieldDefinition|FieldDefinitionNode {
        return $this->field;
    }

    public function getArgument(): InputValueDefinitionNode|FieldArgument {
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
