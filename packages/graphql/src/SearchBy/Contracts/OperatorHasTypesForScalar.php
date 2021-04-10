<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts;

/**
 * Indicates that {@link \LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\BaseOperator} is
 * complex and define own input type for each scalar type.
 */
interface OperatorHasTypesForScalar {
    /**
     * @return array<string,\GraphQL\Language\AST\TypeDefinitionNode>
     */
    public function getTypeDefinitionsForScalar(string $prefix, string $scalar): array;
}
