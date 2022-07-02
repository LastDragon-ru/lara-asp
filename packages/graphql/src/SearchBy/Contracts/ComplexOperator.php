<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Manipulator;

/**
 * Complex operator.
 */
interface ComplexOperator extends Operator {
    public function getDefinition(
        Manipulator $ast,
        InputValueDefinitionNode|InputObjectField $field,
        InputObjectTypeDefinitionNode|InputObjectType $type,
        string $name,
        bool $nullable,
    ): InputObjectTypeDefinitionNode;
}
