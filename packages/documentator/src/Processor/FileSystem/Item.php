<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Core\Path\Path;

use function sprintf;

/**
 * @template TPath of Path
 */
abstract class Item {
    public function __construct(
        /**
         * @var TPath
         */
        protected readonly Path $path,
    ) {
        if (!$this->path->isNormalized()) {
            throw new InvalidArgumentException(
                sprintf(
                    'Path must be normalized, `%s` given.',
                    $this->path,
                ),
            );
        }

        if (!$this->path->isAbsolute()) {
            throw new InvalidArgumentException(
                sprintf(
                    'Path must be absolute, `%s` given.',
                    $this->path,
                ),
            );
        }
    }

    /**
     * @return TPath
     */
    public function getPath(): Path {
        return $this->path;
    }

    public function getName(): string {
        return $this->path->getName();
    }

    public function getFilePath(string $path): FilePath {
        return $this->path->getFilePath($path);
    }

    public function getDirectoryPath(?string $path = null): DirectoryPath {
        return $this->path->getDirectoryPath($path);
    }

    /**
     * @template P of Path
     * @template T of self<P>|Path
     *
     * @param T $path
     *
     * @return (T is self<P> ? new<P> : new<T>)
     */
    public function getRelativePath(self|Path $path): Path {
        $path = $path instanceof Path ? $path : $path->path;
        $path = $this->path->getRelativePath($path);

        return $path;
    }

    /**
     * @param Item<*> $object
     */
    public function isEqual(self $object): bool {
        return ($this === $object) || ($this::class === $object::class && $this->path->isEqual($object->path));
    }
}
