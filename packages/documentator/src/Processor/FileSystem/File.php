<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Caster;

/**
 * @extends Entry<FilePath>
 */
class File extends Entry {
    public function __construct(
        FilePath $path,
        private readonly Caster $caster,
    ) {
        parent::__construct($path);
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
        return $this->caster->castTo($this, $class);
    }
}
