<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts;

/**
 * Operator.
 *
 * @see \LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeDefinitionProvider
 */
interface Operator {
    public static function getName(): string;

    public static function getDirectiveName(): string;

    public function getDefinition(TypeProvider $provider, string $scalar, bool $nullable): string;
}
