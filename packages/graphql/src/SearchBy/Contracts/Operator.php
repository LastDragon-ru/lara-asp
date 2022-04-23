<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts;

use GraphQL\Language\AST\DirectiveNode;

/**
 * Operator.
 *
 * @see \LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeDefinitionProvider
 */
interface Operator {
    /**
     * Must be a valid GraphQL Object Field name.
     */
    public static function getName(): string;

    /**
     * Must start with `@` and be a valid GraphQL Directive name. Defines the
     * default directive that will be used to handle.
     */
    public static function getDirectiveName(): string;

    public function getFieldType(TypeProvider $provider, string $type): string;

    public function getFieldDescription(): string;

    public function getFieldDirective(): ?DirectiveNode;
}
