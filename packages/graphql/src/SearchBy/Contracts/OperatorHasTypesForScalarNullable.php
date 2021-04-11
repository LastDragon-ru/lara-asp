<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts;

/**
 * Indicates that operator has its own input type(s) for each scalar type and it
 * differs if nullable.
 *
 * @see \LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator
 */
interface OperatorHasTypesForScalarNullable {
    /**
     * @return array<\GraphQL\Language\AST\TypeDefinitionNode>
     */
    public function getTypeDefinitionsForScalar(string $name, string $type, bool $nullable): array;
}
