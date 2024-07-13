<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

use Generator;
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
     * Performs action on the `$file`.
     *
     * The `bool` value indicates that the task completed successfully (`true`)
     * or failed (`false`).
     *
     * The {@see Generator} means that the task has dependencies (= other files
     * which should be processed before the task). Each returned value will be
     * resolved relative to the directory where the `$file` located, processed,
     * and then send back into the generator.
     *
     * And, finally, the `null`. Special value that will postpone processing
     * until all other files (and their dependencies) are processed. It may be
     * useful, for example, if the task should collect information from all
     * other files. Please note, the `null` can be returned only once, the
     * second time will automatically mark the task as failed.
     *
     * @return Generator<mixed, Dependency<covariant mixed>, mixed, bool>|bool|null
     *      fixme(documentator): The correct type is `Generator<mixed, Dependency<V>, V, bool>|bool|null`
     *          but it is not yet supported by phpstan (see https://github.com/phpstan/phpstan/issues/4245)
     */
    public function __invoke(Directory $root, File $file): Generator|bool|null;
}
