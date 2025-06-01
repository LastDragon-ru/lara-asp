<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Hook;

interface Task {
    /**
     * Returns the file extensions which task is processing. The `*` can be used
     * to process all existing files.
     *
     * @return non-empty-list<string|Hook>
     */
    public static function getExtensions(): array;

    /**
     * Performs action on the `$file`.
     */
    public function __invoke(DependencyResolver $resolver, File $file): void;
}
