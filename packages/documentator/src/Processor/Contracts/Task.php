<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

use Generator;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
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
     *
     * The `bool` value indicates that the task completed successfully (`true`)
     * or failed (`false`).
     *
     * The {@see Generator} means that the task has dependencies (= other files
     * which should be processed before the current). Each returned value will be
     * resolved relative to the directory where the `$file` located, processed,
     * and then send back into the generator.
     *
     * @return Generator<mixed, Dependency<*>, mixed, bool>|bool
     *      fixme(documentator): The correct type is `Generator<mixed, Dependency<V>, V, bool>|bool`
     *          but it is not yet supported by phpstan (see https://github.com/phpstan/phpstan/issues/4245)
     */
    public function __invoke(Directory $root, File $file): Generator|bool;
}
