<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;

/**
 * @template TParameters
 * @template TValue
 */
interface Resolver {
    /**
     * Should return all files on which resolver/instruction depends.
     *
     * @return array<array-key, string>
     */
    public function getDependencies(Context $context, mixed $parameters): array;

    /**
     * Resolves target into the expected type/value.
     *
     * @param TParameters             $parameters
     * @param array<array-key, ?File> $dependencies
     *
     * @return TValue
     */
    public function resolve(Context $context, mixed $parameters, array $dependencies): mixed;
}
