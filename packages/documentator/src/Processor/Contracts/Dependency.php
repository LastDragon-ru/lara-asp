<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\DependencyUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\Directory;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use Stringable;
use Traversable;

/**
 * Task dependency (= another file).
 *
 * @template TValue of Traversable<mixed, Directory|File>|Directory|File|null
 */
interface Dependency extends Stringable {
    /**
     * @throws DependencyUnresolvable
     *
     * @return TValue
     */
    public function __invoke(FileSystem $fs): mixed;
}
