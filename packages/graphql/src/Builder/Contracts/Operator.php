<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Contracts;

use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\OperatorUnsupportedBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\Support\Contracts\Directive;

/**
 * Operator must also implement individual {@see Scope}.
 */
interface Operator extends Directive {
    /**
     * Must be a valid GraphQL Object Field name.
     */
    public static function getName(): string;

    public function isAvailable(TypeProvider $provider, TypeSource $source, Context $context): bool;

    public function getFieldType(TypeProvider $provider, TypeSource $source, Context $context): ?string;

    public function getFieldDescription(): ?string;

    /**
     * @template TBuilder of object
     *
     * @param TBuilder $builder
     *
     * @throws OperatorUnsupportedBuilder if `$builder` is not supported
     *
     * @return TBuilder
     */
    public function call(
        Handler $handler,
        object $builder,
        Field $field,
        Argument $argument,
        Context $context,
    ): object;
}
