<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts;

/**
 * Indicates that operator has its own input type(s) for each scalar type.
 *
 * @see \LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator
 */
interface OperatorHasTypesForScalar {
    /**
     * @return array<string,\GraphQL\Language\AST\TypeDefinitionNode>
     */
    public function getTypeDefinitionsForScalar(string $prefix, string $scalar): array;
}
