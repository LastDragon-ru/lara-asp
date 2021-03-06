<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

/**
 * Indicates that {@link \LastDragon_ru\LaraASP\GraphQL\SearchBy\Operator} is
 * complex and define own input type.
 */
interface OperatorHasType {
    public function getTypeDefinition(string $name, string $type, bool $nullable): string;
}
