<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts;

/**
 * Simple (comparison/logical) operator.
 *
 * @see \LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\OperatorHasTypes
 * @see \LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\OperatorHasTypesForScalar
 * @see \LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\OperatorHasTypesForScalarNullable
 */
interface Operator {
    public function getName(): string;

    /**
     * @param array<string, string> $map
     */
    public function getDefinition(array $map, string $scalar, bool $nullable): string;
}
