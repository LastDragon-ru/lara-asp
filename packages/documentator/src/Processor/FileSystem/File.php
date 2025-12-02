<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Caster;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use LastDragon_ru\Path\Path;

use function sprintf;

/**
 * @property-read non-empty-string  $name
 * @property-read ?non-empty-string $extension
 * @property-read string            $content
 */
class File {
    public function __construct(
        private readonly FileSystem $fs,
        public readonly FilePath $path,
        private readonly Caster $caster,
    ) {
        if (!$this->path->normalized) {
            throw new InvalidArgumentException(
                sprintf(
                    'Path must be normalized, `%s` given.',
                    $this->path,
                ),
            );
        }

        if ($this->path->relative) {
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

    /**
     * @param non-empty-string $path
     */
    public function getFilePath(string $path): FilePath {
        return $this->path->file($path);
    }

    public function getDirectoryPath(?string $path = null): DirectoryPath {
        return $this->path->directory($path);
    }

    /**
     * @return ($path is DirectoryPath ? DirectoryPath|null : FilePath|null)
     */
    public function getRelativePath(self|DirectoryPath|FilePath $path): DirectoryPath|FilePath|null {
        $path = $path instanceof Path ? $path : $path->path;
        $path = $this->path->relative($path);

        return $path;
    }

    /**
     * @deprecated %{VERSION} Will be replaced to property hooks soon.
     */
    public function __isset(string $name): bool {
        return $this->__get($name) !== null;
    }

    /**
     * @deprecated %{VERSION} Will be replaced to property hooks soon.
     */
    public function __get(string $name): mixed {
        return match ($name) {
            'content'   => $this->fs->read($this),
            'extension' => $this->path->extension,
            'name'      => $this->path->name,
            default     => null,
        };
    }
}
