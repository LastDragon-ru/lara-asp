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
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\Traits\FieldArgument;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;

/**
 * @extends Source<NamedTypeNode|ListTypeNode|NonNullTypeNode|Type, InterfaceFieldSource>
 */
class InterfaceFieldArgumentSource extends Source {
    use FieldArgument;

    public function __construct(
        AstManipulator $manipulator,
        InterfaceFieldSource $parent,
        private InputValueDefinitionNode|Argument $argument,
    ) {
        parent::__construct(
            $manipulator,
            $argument instanceof Argument ? $argument->getType() : $argument->type,
            $parent,
        );
    }

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    public function getObject(): InterfaceTypeDefinitionNode|InterfaceType {
        return $this->getParent()->getObject();
    }

    public function getField(): FieldDefinition|FieldDefinitionNode {
        return $this->getParent()->getField();
    }

    public function getArgument(): InputValueDefinitionNode|Argument {
        return $this->argument;
    }
    // </editor-fold>
}
