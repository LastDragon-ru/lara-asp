<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;

interface Task {
    /**
     * Returns the file extensions which task is processing. The `*` can be used
     * to process any file.
     *
     * @return non-empty-list<string>
     */
    public static function getExtensions(): array;

    /**
     * Performs action on the `$file`.
     */
    public function __invoke(DependencyResolver $resolver, File $file): void;
}
