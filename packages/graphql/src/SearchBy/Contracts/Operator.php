<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts;

use GraphQL\Language\AST\DirectiveNode;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\OperatorInvalidArguments;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\OperatorUnsupportedBuilder;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\Support\Contracts\Directive;

/**
 * Operator.
 *
 * @see \LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeDefinitionProvider
 */
interface Operator extends Directive {
    /**
     * Must be a valid GraphQL Object Field name.
     */
    public static function getName(): string;

    /**
     * Must start with `@` and be a valid GraphQL Directive name. Defines the
     * default directive that will be used to handle.
     */
    public static function getDirectiveName(): string;

    public function getFieldType(TypeProvider $provider, string $type): ?string;

    public function getFieldDescription(): string;

    public function getFieldDirective(): ?DirectiveNode;

    public function isBuilderSupported(object $builder): bool;

    /**
     * Modifies Builder.
     *
     * @see OperatorUnsupportedBuilder should be thrown if `$builder` is not supported
     * @see OperatorInvalidArguments should be thrown if `$argument` has an invalid value
     *
     * @template TBuilder of object
     *
     * @param TBuilder      $builder
     * @param array<string> $property
     *
     * @return TBuilder
     */
    public function call(Builder $search, object $builder, array $property, Argument $argument): object;
}
