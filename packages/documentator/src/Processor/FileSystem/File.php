<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Caster;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\FileSystemAdapter;

/**
 * @extends Entry<FilePath>
 */
abstract class File extends Entry {
    public function __construct(
        FileSystemAdapter $adapter,
        FilePath $path,
        private readonly Caster $caster,
    ) {
        parent::__construct($adapter, $path);
    }

    /**
     * @return ?non-empty-string
     */
    public function getExtension(): ?string {
        return $this->path->getExtension();
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public function as(string $class): object {
        return $this->caster->get($this, $class);
    }
}
