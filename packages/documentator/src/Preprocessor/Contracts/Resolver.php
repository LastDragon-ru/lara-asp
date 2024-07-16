<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts;

use Generator;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;

/**
 * @template TTarget
 * @template TParameters of object|null
 */
interface Resolver {
    /**
     * Resolves target into the expected type/value.
     *
     * Generator should be used to resolve any file/directory which the Resolver depends on.
     *
     * @param TParameters $parameters
     *
     * @return Generator<mixed, Dependency<*>, mixed, TTarget>|TTarget
     *       fixme(documentator): The correct type is `Generator<mixed, Dependency<V>, V, TTarget>|TTarget`
     *           but it is not yet supported by phpstan (see https://github.com/phpstan/phpstan/issues/4245)
     */
    public function __invoke(Context $context, mixed $parameters): mixed;
}
