<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Core\Path\Path;
use Override;
use Stringable;

use function is_dir;
use function sprintf;
use function str_starts_with;

class Directory implements Stringable {
    public function __construct(
        private readonly DirectoryPath $path,
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

        if (!is_dir((string) $this->path)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The `%s` is not a directory.',
                    $this->path,
                ),
            );
        }
    }

    public function getPath(): DirectoryPath {
        return $this->path;
    }

    public function getName(): string {
        return $this->path->getName();
    }

    public function isInside(self|File|Path $path): bool {
        $path   = $path instanceof Path ? $path : $path->getPath();
        $path   = (string) $this->path->getPath($path);
        $root   = (string) $this->path;
        $inside = $path !== $root && str_starts_with($path, "{$root}/");

        return $inside;
    }

    public function getFilePath(string $path): FilePath {
        return $this->path->getFilePath($path);
    }

    public function getDirectoryPath(string $path): DirectoryPath {
        return $this->path->getDirectoryPath($path);
    }

    /**
     * @template T of self|File|Path
     *
     * @param T $path
     *
     * @return (T is Path ? new<T> : (T is File ? FilePath : DirectoryPath))
     */
    public function getRelativePath(self|File|Path $path): Path {
        $path = $path instanceof Path ? $path : $path->getPath();
        $path = $this->path->getRelativePath($path);

        return $path;
    }

    #[Override]
    public function __toString(): string {
        return (string) $this->getPath();
    }
}
