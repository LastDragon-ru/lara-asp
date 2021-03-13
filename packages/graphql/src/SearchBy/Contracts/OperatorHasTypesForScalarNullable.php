<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts;

/**
 * Indicates that {@link \LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator} is
 * complex and define own input type for each scalar type and it differs if
 * nullable.
 */
interface OperatorHasTypesForScalarNullable extends Operator {
    /**
     * @return array<\GraphQL\Language\AST\TypeDefinitionNode>
     */
    public function getTypeDefinitionsForScalar(string $name, string $type, bool $nullable): array;
}
