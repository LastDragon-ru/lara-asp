<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\DependencyResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;

/**
 * Performs action on the file.
 */
interface FileTask extends Task {
    /**
     * Returns the file extensions which task is processing. The `*` can be used
     * to process all existing files.
     *
     * @return non-empty-list<string>
     */
    public static function getExtensions(): array;

    public function __invoke(DependencyResolver $resolver, File $file): void;
}
