<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Metadata;

/**
 * @extends Entry<FilePath>
 */
abstract class File extends Entry {
    public function __construct(
        Adapter $adapter,
        FilePath $path,
        private readonly Metadata $metadata,
    ) {
        parent::__construct($adapter, $path);
    }

    /**
     * @return ?non-empty-string
     */
    public function getExtension(): ?string {
        return $this->path->getExtension();
    }

    abstract public function getContent(): string;

    /**
     * @template T of object
     *
     * @param class-string<T> $metadata
     *
     * @return T
     */
    public function as(string $metadata): object {
        return $this->metadata->get($this, $metadata);
    }
}
