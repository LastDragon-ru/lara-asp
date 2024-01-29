<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Traits;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceFieldArgumentSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldArgumentSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectSource;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;

trait WithSource {
    /**
     * @return ($type is InterfaceTypeDefinitionNode ? InterfaceSource : ObjectSource)
     */
    protected function getTypeSource(
        AstManipulator $manipulator,
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode $type,
    ): InterfaceSource|ObjectSource {
        return $type instanceof InterfaceTypeDefinitionNode
            ? new InterfaceSource($manipulator, $type)
            : new ObjectSource($manipulator, $type);
    }

    /**
     * @return ($type is InterfaceTypeDefinitionNode ? InterfaceFieldSource : ObjectFieldSource)
     */
    protected function getFieldSource(
        AstManipulator $manipulator,
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode $type,
        FieldDefinitionNode $field,
    ): ObjectFieldSource|InterfaceFieldSource {
        return $this->getTypeSource($manipulator, $type)->getField($field);
    }

    /**
     * @return ($type is InterfaceTypeDefinitionNode ? InterfaceFieldArgumentSource : ObjectFieldArgumentSource)
     */
    protected function getFieldArgumentSource(
        AstManipulator $manipulator,
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode $type,
        FieldDefinitionNode $field,
        InputValueDefinitionNode $argument,
    ): ObjectFieldArgumentSource|InterfaceFieldArgumentSource {
        return $this->getFieldSource($manipulator, $type, $field)->getArgument($argument);
    }
}
