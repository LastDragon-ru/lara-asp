<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Utils\Path;
use Override;
use SplFileInfo;
use Stringable;

use function basename;
use function dirname;
use function is_dir;
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

    public function getPath(?string $path = null): string {
        return $path !== null ? Path::getPath($this->path, $path) : $this->path;
    }

    public function getName(): string {
        return basename($this->path);
    }

    public function isWritable(): bool {
        return $this->writable && is_writable($this->path);
    }

    public function isInside(SplFileInfo|File|self|string $path): bool {
        $path = match (true) {
            $path instanceof SplFileInfo => $this->getPath($path->getPathname()),
            is_object($path)             => $path->getPath(),
            default                      => $this->getPath($path),
        };
        $inside = $path !== $this->path
            && str_starts_with($path, $this->path);

        return $inside;
    }

    public function getRelativePath(self|File $root): string {
        $root = $root instanceof File ? dirname($root->getPath()) : $root->getPath();
        $path = Path::getRelativePath($root, $this->path);

        return $path;
    }

    #[Override]
    public function __toString(): string {
        return $this->getPath();
    }
}
