<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use Closure;
use InvalidArgumentException;
use Iterator;
use LastDragon_ru\LaraASP\Core\Utils\Path;
use Override;
use SplFileInfo;
use Stringable;
use Symfony\Component\Finder\Finder;

use function basename;
use function dirname;
use function is_dir;
use function is_file;
use function is_object;
use function is_writable;
use function sprintf;
use function str_starts_with;

class Directory implements Stringable {
    public function __construct(
        private readonly string $path,
        private readonly bool $writable,
    ) {
        if (!Path::isNormalized($this->path)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Path must be normalized, `%s` given.',
                    $this->path,
                ),
            );
        }

        if (!Path::isAbsolute($this->path)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Path must be absolute, `%s` given.',
                    $this->path,
                ),
            );
        }

        if (!is_dir($this->path)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The `%s` is not a directory.',
                    $this->path,
                ),
            );
        }
    }

    public function getPath(): string {
        return $this->path;
    }

    public function getName(): string {
        return basename($this->path);
    }

    public function isWritable(): bool {
        return $this->writable && is_writable($this->path);
    }

    public function getFile(SplFileInfo|File|string $path): ?File {
        // Object?
        if ($path instanceof SplFileInfo) {
            $path = $path->getPathname();
        } elseif ($path instanceof File) {
            $path = $path->getPath();
        } else {
            // empty
        }

        // File?
        $path = Path::getPath($this->path, $path);

        if (!is_file($path)) {
            return null;
        }

        // Create
        $writable = $this->writable && $this->isInside($path);
        $file     = new File($path, $writable);

        return $file;
    }

    public function getDirectory(SplFileInfo|self|File|string $path): ?self {
        // Object?
        if ($path instanceof SplFileInfo) {
            $path = $path->getPathname();
        } elseif ($path instanceof File) {
            $path = dirname($path->getPath());
        } elseif ($path instanceof self) {
            $path = $path->getPath();
        } else {
            // empty
        }

        // Self?
        if ($path === '.' || $path === '') {
            return $this;
        }

        // Directory?
        $path = Path::getPath($this->path, $path);

        if (!is_dir($path)) {
            return null;
        }

        // Create
        $writable = $this->writable && $this->isInside($path);
        $dir      = $this->path !== $path
            ? new self($path, $writable)
            : $this;

        return $dir;
    }

    public function isInside(SplFileInfo|File|self|string $path): bool {
        $path = match (true) {
            $path instanceof SplFileInfo => Path::getPath($this->path, $path->getPathname()),
            is_object($path)             => $path->getPath(),
            default                      => Path::getPath($this->path, $path),
        };
        $inside = $path !== $this->path
            && str_starts_with($path, $this->path);

        return $inside;
    }

    /**
     * @param array<array-key, string>|string|null         $patterns {@see Finder::name()}
     * @param array<array-key, string|int>|string|int|null $depth    {@see Finder::depth()}
     *
     * @return Iterator<array-key, File>
     */
    public function getFilesIterator(
        array|string|null $patterns = null,
        array|string|int|null $depth = null,
    ): Iterator {
        yield from $this->getIterator($this->getFile(...), $patterns, $depth);
    }

    /**
     * @param array<array-key, string>|string|null         $patterns {@see Finder::name()}
     * @param array<array-key, string|int>|string|int|null $depth    {@see Finder::depth()}
     *
     * @return Iterator<array-key, Directory>
     */
    public function getDirectoriesIterator(
        array|string|null $patterns = null,
        array|string|int|null $depth = null,
    ): Iterator {
        yield from $this->getIterator($this->getDirectory(...), $patterns, $depth);
    }

    /**
     * @template T of object
     *
     * @param Closure(SplFileInfo): ?T                     $factory
     * @param array<array-key, string>|string|null         $patterns {@see Finder::name()}
     * @param array<array-key, string|int>|string|int|null $depth    {@see Finder::depth()}
     *
     * @return Iterator<array-key, T>
     */
    protected function getIterator(
        Closure $factory,
        array|string|null $patterns = null,
        array|string|int|null $depth = null,
    ): Iterator {
        $finder = Finder::create()
            ->ignoreVCSIgnored(true)
            ->exclude('node_modules')
            ->exclude('vendor')
            ->in($this->path)
            ->sortByName(true);

        if ($patterns !== null) {
            $finder = $finder->name($patterns);
        }

        if ($depth !== null) {
            $finder = $finder->depth($depth);
        }

        foreach ($finder as $info) {
            $item = $factory($info);

            if ($item !== null) {
                yield $item;
            }
        }

        yield from [];
    }

    public function getRelativePath(self $root): string {
        return Path::getRelativePath($root->getPath(), $this->path);
    }

    #[Override]
    public function __toString(): string {
        return $this->getPath();
    }
}
