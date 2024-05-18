<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;

interface Task {
    /**
     * Returns the file extensions which task is processing.
     *
     * @return non-empty-list<string>
     */
    public function getExtensions(): array;

    /**
     * Should return all files on which `$file` depends.
     *
     * @return array<array-key, string>
     */
    public function getDependencies(Directory $directory, File $file): array;

    /**
     * Performs action on the `$file`.
     *
     * @param array<array-key, File> $dependencies
     */
    public function run(Directory $directory, File $file, array $dependencies): bool;
}
