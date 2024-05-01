<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Contracts;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Utils\TypeReference;

interface TypeDefinition {
    /**
     * Returns the type name for given Builder and Source.
     */
    public function getTypeName(TypeSource $source, Context $context): string;

    /**
     * Returns the type definition for given Source if possible. The name must be equal to `$name`.
     *
     * @see TypeReference
     *
     * @return (TypeDefinitionNode&Node)|(Type&NamedType)|null Returning {@see Type} is deprecated, please use
     *      {@see TypeReference} instead.
     */
    public function getTypeDefinition(
        Manipulator $manipulator,
        TypeSource $source,
        Context $context,
        string $name,
    ): TypeDefinitionNode|Type|null;
}
