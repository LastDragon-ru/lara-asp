<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Tasks;

use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Task;

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

    public function __invoke(Resolver $resolver, File $file): void;
}
