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

    public function getFieldType(TypeProvider $provider, string $type): string;

    public function getFieldDescription(): string;
}
