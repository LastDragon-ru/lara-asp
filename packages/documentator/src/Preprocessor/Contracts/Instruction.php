<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts;

use Generator;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use SplFileInfo;

/**
 * @template TTarget
 * @template TParameters of object|null
 */
interface Instruction {
    public static function getName(): string;

    /**
     * @return class-string<Resolver<TParameters, TTarget>|Resolver<null, TTarget>>
     */
    public static function getResolver(): string;

    /**
     * @return class-string<object>|null
     *      fixme(documentator): The correct type is `(TParameters is object ? class-string<TParameters> : null)`
     *          but it is not yet supported by phpstan (see https://github.com/phpstan/phpstan/issues/10553)
     */
    public static function getParameters(): ?string;

    /**
     * Process target with parameters and return result.
     *
     * Generator should be used to resolve any file which the Resolver depends on.
     *
     * @param TTarget     $target
     * @param TParameters $parameters
     *
     * @return Generator<mixed, SplFileInfo|File|string, File, string>|string
     */
    public function __invoke(Context $context, mixed $target, mixed $parameters): Generator|string;
}
