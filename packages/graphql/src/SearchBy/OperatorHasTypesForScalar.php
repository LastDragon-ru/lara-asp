<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

/**
 * Indicates that {@link \LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\BaseOperator} is
 * complex and define own input type for each scalar type.
 */
interface OperatorHasTypesForScalar extends Operator {
    /**
     * @return array<\GraphQL\Language\AST\TypeDefinitionNode>
     */
    public function getTypeDefinitionsForScalar(string $name, string $type): array;
}
