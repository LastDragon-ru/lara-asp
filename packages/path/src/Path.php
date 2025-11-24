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
 *
 * @phpstan-sealed DirectoryPath|FilePath
 */
abstract class Path implements Stringable {
    protected ?bool $isNormalized = null; // `private` will lead to an error https://github.com/phpstan/phpstan/issues/13836
    protected ?bool $isAbsolute   = null; // `private` will lead to an error https://github.com/phpstan/phpstan/issues/13836

    public function __construct(
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
     * @return ($path is DirectoryPath ? DirectoryPath : FilePath)
     */
    public function resolve(self $path): self {
        if ($path->relative) {
            $resolved = SymfonyPath::join($this->directory()->path, $path->path);
            $resolved = $path instanceof DirectoryPath ? new DirectoryPath($resolved) : new FilePath($resolved);
        } else {
            $resolved = $path;
        }

        return $resolved->normalized();
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
     * @return ($path is DirectoryPath ? DirectoryPath : FilePath)
     */
    public function relative(self $path): self {
        $relative             = $this->resolve($path);
        $relative             = SymfonyPath::makeRelative($relative->path, $this->directory()->path);
        $relative             = $path instanceof DirectoryPath ? new DirectoryPath($relative) : new FilePath($relative);
        $relative             = $relative->normalized();
        $relative->isAbsolute = false;

        return $relative;
    }

    /**
     * @return ($this is DirectoryPath ? DirectoryPath : FilePath)
     */
    public function normalized(): self {
        if ($this->normalized) {
            // @phpstan-ignore return.type (sealed not narrowed correctly, see https://github.com/phpstan/phpstan/issues/13839)
            return $this;
        }

        $path               = $this->normalize($this->path);
        $path               = $this instanceof DirectoryPath ? new DirectoryPath($path) : new FilePath($path);
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
