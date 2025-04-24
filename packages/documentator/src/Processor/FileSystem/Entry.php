<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Core\Path\Path;
use Override;
use Stringable;

use function sprintf;

/**
 * @template TPath of DirectoryPath|FilePath
 */
abstract class Entry implements Stringable {
    public function __construct(
        protected readonly Adapter $adapter,
        /**
         * @var TPath
         */
        protected readonly DirectoryPath|FilePath $path,
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
    public function getPath(): DirectoryPath|FilePath {
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
     * @template P of DirectoryPath|FilePath
     *
     * @param self<P>|DirectoryPath|FilePath $path
     *
     * @return ($path is DirectoryPath ? DirectoryPath : ($path is FilePath ? FilePath : P))
     */
    public function getRelativePath(self|DirectoryPath|FilePath $path): DirectoryPath|FilePath {
        $path = $path instanceof Path ? $path : $path->path;
        $path = $this->path->getRelativePath($path);

        return $path;
    }

    /**
     * @param Entry<*> $object
     */
    public function isEqual(self $object): bool {
        return ($this === $object) || ($this::class === $object::class && $this->path->isEqual($object->path));
    }

    #[Override]
    public function __toString(): string {
        return (string) $this->path;
    }
}
