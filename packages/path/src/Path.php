<?php declare(strict_types = 1);

namespace LastDragon_ru\Path;

use Override;
use Stringable;
use Symfony\Component\Filesystem\Path as SymfonyPath;

use function basename;

/**
 * @property-read string $name
 * @property-read bool   $absolute
 * @property-read bool   $relative
 * @property-read bool   $normalized
 */
abstract class Path implements Stringable {
    private ?bool $isNormalized = null;
    private ?bool $isAbsolute   = null;

    final public function __construct(
        public readonly string $path,
    ) {
        // empty
    }

    #[Override]
    public function __toString(): string {
        return $this->path;
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
            'name'       => basename($this->path),
            'relative'   => !$this->absolute,
            'absolute'   => $this->isAbsolute   ??= SymfonyPath::isAbsolute($this->path),
            'normalized' => $this->isNormalized ??= $this->normalize($this->path) === $this->path,
            default      => null,
        };
    }

    /**
     * @template T of Path
     *
     * @param T $path
     *
     * @return new<T>
     */
    public function resolve(self $path): self {
        if ($path->relative) {
            $class = $path::class;
            $path  = SymfonyPath::join($this->directory()->path, $path->path);
            $path  = new $class($path);
        }

        return $path->normalized();
    }

    public function parent(): DirectoryPath {
        return (new DirectoryPath("{$this->path}/.."))->normalized();
    }

    public function file(string $path): FilePath {
        return $this->resolve(new FilePath($path));
    }

    public function directory(?string $path = null): DirectoryPath {
        return match (true) {
            $path === null && $this instanceof DirectoryPath => $this->normalized(),
            $path !== null                                   => $this->resolve(new DirectoryPath($path)),
            default                                          => $this->parent(),
        };
    }

    /**
     * @template T of Path
     *
     * @param T $path
     *
     * @return new<T>
     */
    public function relative(self $path): self {
        $class            = $path::class;
        $path             = $this->resolve($path);
        $path             = SymfonyPath::makeRelative($path->path, $this->directory()->path);
        $path             = (new $class($path))->normalized();
        $path->isAbsolute = false;

        return $path;
    }

    public function normalized(): static {
        if ($this->normalized) {
            return $this;
        }

        $path               = new static($this->normalize($this->path));
        $path->isNormalized = true;

        return $path;
    }

    public function equals(self $path): bool {
        return $path instanceof $this
            && $path->normalized()->path === $this->normalized()->path;
    }

    protected function normalize(string $path): string {
        return SymfonyPath::canonicalize($path);
    }
}
