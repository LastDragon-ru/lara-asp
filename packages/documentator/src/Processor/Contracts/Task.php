<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

use Generator;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use SplFileInfo;

interface Task {
    /**
     * Returns the file extensions which task is processing.
     *
     * @return non-empty-list<string>
     */
    public function getExtensions(): array;

    /**
     * Performs action on the `$file`.
     *
     * Each returned value will be treated as a dependency of the task. It will
     * be resolved relative to the directory where the `$file` located,
     * processed, and then send back into the generator.
     *
     * @return Generator<mixed, SplFileInfo|File|string, ?File, bool>|bool
     */
    public function __invoke(Directory $root, File $file): Generator|bool;
}
