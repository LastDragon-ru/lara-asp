<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Contracts;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;

interface TypeDefinition {
    /**
     * Returns the type name for given Builder and Source.
     */
    public function getTypeName(Manipulator $manipulator, BuilderInfo $builder, TypeSource $source): string;

    /**
     * Returns the type definition for given Source if possible. The name must be equal to `$name`.
     *
     * @return (TypeDefinitionNode&Node)|(Type&NamedType)|null
     */
    public function getTypeDefinition(
        Manipulator $manipulator,
        string $name,
        TypeSource $source,
    ): TypeDefinitionNode|Type|null;
}
