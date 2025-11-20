<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Core\Path\Path;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Caster;
use Override;
use Stringable;

use function sprintf;

class File implements Stringable {
    public function __construct(
        public readonly FilePath $path,
        private readonly Caster $caster,
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
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public function as(string $class): object {
        return $this->caster->castTo($this, $class);
    }

    public function getName(): string {
        return $this->path->getName();
    }

    /**
     * @return ?non-empty-string
     */
    public function getExtension(): ?string {
        return $this->path->getExtension();
    }

    public function getFilePath(string $path): FilePath {
        return $this->path->getFilePath($path);
    }

    public function getDirectoryPath(?string $path = null): DirectoryPath {
        return $this->path->getDirectoryPath($path);
    }

    /**
     * @return ($path is DirectoryPath ? DirectoryPath : FilePath)
     */
    public function getRelativePath(self|DirectoryPath|FilePath $path): DirectoryPath|FilePath {
        $path = $path instanceof Path ? $path : $path->path;
        $path = $this->path->getRelativePath($path);

        return $path;
    }

    public function isEqual(self $object): bool {
        return ($this === $object) || ($this::class === $object::class && $this->path->isEqual($object->path));
    }

    #[Override]
    public function __toString(): string {
        return (string) $this->path;
    }
}
