<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Sources;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\Traits\Field;

/**
 * @extends Source<NamedTypeNode|ListTypeNode|NonNullTypeNode|Type>
 */
class ObjectFieldSource extends Source {
    use Field;

    public function __construct(
        Manipulator $manipulator,
        private ObjectTypeDefinitionNode|ObjectType $object,
        private FieldDefinitionNode|FieldDefinition $field,
    ) {
        parent::__construct($manipulator, $field instanceof FieldDefinition ? $field->getType() : $field->type);
    }

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    public function getObject(): ObjectTypeDefinitionNode|ObjectType {
        return $this->object;
    }

    public function getField(): FieldDefinition|FieldDefinitionNode {
        return $this->field;
    }
    // </editor-fold>
}
