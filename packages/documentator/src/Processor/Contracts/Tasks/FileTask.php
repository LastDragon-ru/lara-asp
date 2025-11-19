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
     * Glob pattern(s) to search files that should be processed by the task. It
     * will be matched against the name of the file and thus cannot contain
     * the `/`.
     *
     * @return non-empty-list<non-empty-string>|non-empty-string
     */
    public static function glob(): array|string;

    public function __invoke(DependencyResolver $resolver, File $file): void;
}
