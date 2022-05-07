<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts;

use GraphQL\Language\AST\DirectiveNode;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\OperatorUnsupportedBuilder;
use LastDragon_ru\LaraASP\GraphQL\Utils\Property;
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
     * @template TBuilder of object
     *
     * @param TBuilder $builder
     * @param Property $property
     *
     * @throws OperatorUnsupportedBuilder if `$builder` is not supported
     *
     * @return TBuilder
     */
    public function call(Builder $search, object $builder, Property $property, Argument $argument): object;
}
