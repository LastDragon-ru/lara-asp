<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Sources;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\Traits\Field;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;

/**
 * @extends Source<NamedTypeNode|ListTypeNode|NonNullTypeNode|Type>
 */
class InputFieldSource extends Source {
    use Field;

    public function __construct(
        AstManipulator $manipulator,
        private InputObjectTypeDefinitionNode|InputObjectType $object,
        private InputValueDefinitionNode|InputObjectField $field,
    ) {
        parent::__construct($manipulator, $field instanceof InputObjectField ? $field->getType() : $field->type);
    }

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    public function getObject(): InputObjectTypeDefinitionNode|InputObjectType {
        return $this->object;
    }

    public function getField(): InputValueDefinitionNode|InputObjectField {
        return $this->field;
    }

    public function hasArguments(): bool {
        return false;
    }
    // </editor-fold>
}
