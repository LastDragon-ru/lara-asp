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
interface Resolver {
    /**
     * Resolves target into the expected type/value.
     *
     * Generator should be used to resolve any file which the Resolver depends on.
     *
     * @param TParameters $parameters
     *
     * @return Generator<mixed, SplFileInfo|File|string, File, TTarget>|TTarget
     */
    public function __invoke(Context $context, mixed $parameters): mixed;
}
