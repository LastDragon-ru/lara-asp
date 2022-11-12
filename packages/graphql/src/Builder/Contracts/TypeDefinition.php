<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Contracts;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeDefinitionNode;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;

interface TypeDefinition {
    public static function getTypeName(BuilderInfo $builder, string $type = null, bool $nullable = null): string;

    /**
     * @return (TypeDefinitionNode&Node)|null
     */
    public function getTypeDefinitionNode(
        Manipulator $manipulator,
        string $name,
        string $type = null,
        bool $nullable = null,
    ): ?TypeDefinitionNode;
}
