<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts;

use Generator;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Dependency;

/**
 * @template TParameters of Parameters|null
 */
interface Instruction {
    public static function getName(): string;

    /**
     * @return class-string<Parameters>|null
     *      fixme(documentator): The correct type is `(TParameters is Parameters ? class-string<TParameters> : null)`
     *          but it is not yet supported by phpstan (see https://github.com/phpstan/phpstan/issues/10553)
     */
    public static function getParameters(): ?string;

    /**
     * Process target with parameters and return result.
     *
     * Generator should be used to resolve any file which the Instruction depends on.
     *
     * @param TParameters $parameters
     *
     * @return Generator<mixed, Dependency<*>, mixed, string>|string
     *      fixme(documentator): The correct type is `Generator<mixed, Dependency<V>, V, string>|string`
     *           but it is not yet supported by phpstan (see https://github.com/phpstan/phpstan/issues/4245)
     */
    public function __invoke(Context $context, string $target, mixed $parameters): Generator|string;
}
