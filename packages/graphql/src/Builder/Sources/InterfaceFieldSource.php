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
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\Traits\Field;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;

/**
 * @extends Source<NamedTypeNode|ListTypeNode|NonNullTypeNode|Type, InterfaceSource>
 */
class InterfaceFieldSource extends Source {
    use Field;

    public function __construct(
        AstManipulator $manipulator,
        InterfaceSource $parent,
        private FieldDefinitionNode|FieldDefinition $field,
    ) {
        parent::__construct(
            $manipulator,
            $field instanceof FieldDefinition ? $field->getType() : $field->type,
            $parent,
        );
    }

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    public function getObject(): InterfaceTypeDefinitionNode|InterfaceType {
        return $this->getParent()->getType();
    }

    public function getField(): FieldDefinition|FieldDefinitionNode {
        return $this->field;
    }
    // </editor-fold>

    // <editor-fold desc="Helpers">
    // =================================================================================================================
    public function getArgument(InputValueDefinitionNode|Argument $argument): InterfaceFieldArgumentSource {
        return new InterfaceFieldArgumentSource($this->getManipulator(), $this, $argument);
    }
    // </editor-fold>
}
