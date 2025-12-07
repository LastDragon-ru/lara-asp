<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Caster;
use LastDragon_ru\Path\FilePath;

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
