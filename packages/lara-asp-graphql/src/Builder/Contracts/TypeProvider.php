<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Contracts;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\Type;

interface TypeProvider {
    /**
     * @param class-string<TypeDefinition> $definition
     */
    public function getType(string $definition, TypeSource $source, Context $context): string;

    public function getTypeSource((TypeDefinitionNode&Node)|(TypeNode&Node)|Type $type): TypeSource;
}
