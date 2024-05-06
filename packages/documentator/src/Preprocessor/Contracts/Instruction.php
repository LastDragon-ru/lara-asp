<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;

/**
 * @template TTarget
 * @template TParameters of object|null
 */
interface Instruction {
    public static function getName(): string;

    /**
     * @return class-string<TargetResolver<TParameters, TTarget>|TargetResolver<null, TTarget>>
     */
    public static function getTarget(): string;

    /**
     * @return class-string<object>|null
     *      fixme(documentator): The correct type is `(TParameters is object ? class-string<TParameters> : null)`
     *          but it is not yet supported by phpstan (see https://github.com/phpstan/phpstan/issues/10553)
     */
    public static function getParameters(): ?string;

    /**
     * @param Context     $context
     * @param TTarget     $target
     * @param TParameters $parameters
     */
    public function process(Context $context, mixed $target, mixed $parameters): string;
}
