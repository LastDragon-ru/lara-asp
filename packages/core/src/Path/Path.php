<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Path;

use Override;
use Stringable;
use Symfony\Component\Filesystem\Path as SymfonyPath;

use function basename;
use function preg_match;

abstract class Path implements Stringable {
    private ?bool $normalized = null;
    private ?bool $absolute   = null;

    final public function __construct(
        public readonly string $path,
    ) {
        // empty
    }

    #[Override]
    public function __toString(): string {
        return $this->path;
    }

    public function getName(): string {
        return basename($this->path);
    }

    /**
     * @template T of Path
     *
     * @param T $path
     *
     * @return new<T>
     */
    public function getPath(self $path): self {
        if ($path->isRelative()) {
            $class = $path::class;
            $path  = SymfonyPath::join((string) $this->getDirectory(), (string) $path);
            $path  = new $class($path);
        }

        return $path->getNormalizedPath();
    }

    abstract public function getParentPath(): DirectoryPath;

    public function getFilePath(string $path): FilePath {
        return $this->getPath(new FilePath($path));
    }

    public function getDirectoryPath(?string $path = null): DirectoryPath {
        return $path === null
            ? $this->getDirectory()->getNormalizedPath()
            : $this->getPath(new DirectoryPath($path));
    }

    /**
     * @template T of Path
     *
     * @param T $path
     *
     * @return new<T>
     */
    public function getRelativePath(self $path): self {
        $directory      = $this->getDirectory();
        $class          = $path::class;
        $path           = $this->getPath($path);
        $path           = SymfonyPath::makeRelative((string) $path, (string) $directory);
        $path           = (new $class($path))->getNormalizedPath();
        $path->absolute = false;

        return $path;
    }

    abstract protected function getDirectory(): DirectoryPath;

    public function getNormalizedPath(): static {
        if ($this->isNormalized()) {
            return $this;
        }

        $path             = new static(SymfonyPath::canonicalize($this->path));
        $path->normalized = true;

        return $path;
    }

    public function isRelative(): bool {
        return !$this->isAbsolute();
    }

    public function isAbsolute(): bool {
        if ($this->absolute === null) {
            $this->absolute = SymfonyPath::isAbsolute($this->path);
        }

        return $this->absolute;
    }

    public function isNormalized(): bool {
        if ($this->normalized === null) {
            $this->normalized = SymfonyPath::canonicalize($this->path) === $this->path;
        }

        return $this->normalized;
    }

    public function isEqual(self $path): bool {
        return $path instanceof $this
            && (string) $path->getNormalizedPath() === (string) $this->getNormalizedPath();
    }

    public function isMatch(string $regexp): bool {
        return preg_match($regexp, $this->path) > 0;
    }
}
