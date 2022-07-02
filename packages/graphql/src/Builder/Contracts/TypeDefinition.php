<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Contracts;

use GraphQL\Language\AST\TypeDefinitionNode;

interface TypeDefinition {
    public static function getName(): string;

    /**
     * @return (TypeDefinitionNode&\GraphQL\Language\AST\Node)|null
     */
    public function getTypeDefinitionNode(
        string $name,
        string $scalar = null,
        bool $nullable = null,
    ): ?TypeDefinitionNode;
}
