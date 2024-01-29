<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Sources;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\Argument;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\Traits\Field;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;

/**
 * @extends Source<(TypeNode&Node)|Type, ObjectSource>
 */
class ObjectFieldSource extends Source {
    use Field;

    /**
     * @param (TypeNode&Node)|Type|null $type
     */
    public function __construct(
        AstManipulator $manipulator,
        ObjectSource $parent,
        private FieldDefinitionNode|FieldDefinition $field,
        TypeNode|Type|null $type = null,
    ) {
        parent::__construct(
            $manipulator,
            $type ?? ($field instanceof FieldDefinition ? $field->getType() : $field->type),
            $parent,
        );
    }

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    public function getObject(): ObjectTypeDefinitionNode|ObjectType {
        return $this->getParent()->getType();
    }

    public function getField(): FieldDefinition|FieldDefinitionNode {
        return $this->field;
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =================================================================================================================
    /**
     * @param (TypeNode&Node)|Type|null $type
     */
    public function getArgument(
        InputValueDefinitionNode|Argument $argument,
        TypeNode|Type $type = null,
    ): ObjectFieldArgumentSource {
        return new ObjectFieldArgumentSource($this->getManipulator(), $this, $argument, $type);
    }
    // </editor-fold>
}
