<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyNotFound;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Stringable;

/**
 * Task dependency (= another file).
 *
 * @template TValue
 */
interface Dependency extends Stringable {
    /**
     * @throws DependencyNotFound
     *
     * @return TValue
     */
    public function __invoke(Directory $root, File $file): mixed;
}
