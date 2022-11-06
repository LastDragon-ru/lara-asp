<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Contracts;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeDefinitionNode;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;

interface TypeDefinition {
    public static function getName(BuilderInfo $builder, string $type = null, bool $nullable = null): string;

    /**
     * @return (TypeDefinitionNode&Node)|null
     */
    public function getTypeDefinitionNode(
        string $name,
        string $type = null,
        bool $nullable = null,
    ): ?TypeDefinitionNode;
}
