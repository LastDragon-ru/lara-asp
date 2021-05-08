<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts;

/**
 * Simple (comparison/logical) operator.
 *
 * @see \LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeDefinitionProvider
 */
interface Operator {
    public function getName(): string;

    public function getDefinition(TypeProvider $provider, string $scalar, bool $nullable): string;
}
