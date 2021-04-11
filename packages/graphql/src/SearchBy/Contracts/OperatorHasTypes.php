<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts;

/**
 * Indicates that operator has its own input type(s).
 *
 * @see \LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator
 * @see \LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComplexOperator
 */
interface OperatorHasTypes {
    /**
     * @return array<string,\GraphQL\Language\AST\TypeDefinitionNode>
     */
    public function getTypeDefinitions(string $prefix): array;
}
