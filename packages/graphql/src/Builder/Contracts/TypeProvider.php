<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Contracts;

use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Type\Definition\Type;

interface TypeProvider {
    /**
     * @param class-string<TypeDefinition> $definition
     */
    public function getType(string $definition, TypeSource $source): string;

    /**
     * @param (TypeDefinitionNode&Node)|NamedTypeNode|ListTypeNode|NonNullTypeNode|Type $type
     */
    public function getTypeSource(TypeDefinitionNode|NamedTypeNode|ListTypeNode|NonNullTypeNode|Type $type): TypeSource;
}
