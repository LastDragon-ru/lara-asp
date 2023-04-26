<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Contracts;

use GraphQL\Language\AST\DirectiveNode;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\OperatorUnsupportedBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\Support\Contracts\Directive;

interface Operator extends Directive {
    /**
     * Must be a valid GraphQL Object Field name.
     */
    public static function getName(): string;

    /**
     * Must start with `@` and be a valid GraphQL Directive name.
     *
     * @deprecated 4.1.0 Directive name will be determined by a class name same
     *      as Lighthouse does. So the method is not needed anymore and will be
     *      removed in the next major version.
     */
    public static function getDirectiveName(): string;

    public function getFieldType(TypeProvider $provider, TypeSource $source): string;

    public function getFieldDescription(): string;

    public function getFieldDirective(): ?DirectiveNode;

    /**
     * @param class-string $builder
     */
    public function isBuilderSupported(string $builder): bool;

    /**
     * @template TBuilder of object
     *
     * @param TBuilder $builder
     *
     * @throws OperatorUnsupportedBuilder if `$builder` is not supported
     *
     * @return TBuilder
     */
    public function call(Handler $handler, object $builder, Property $property, Argument $argument): object;
}
