<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File as Contract;
use LastDragon_ru\Path\FilePath;

use function sprintf;

/**
 * @internal
 */
class File implements Contract {
    public function __construct(
        private readonly FileSystem $fs,
        public readonly FilePath $path,
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
     * @deprecated 10.0.0 Will be replaced to property hooks soon.
     */
    public function __isset(string $name): bool {
        return $this->__get($name) !== null;
    }

    /**
     * @deprecated 10.0.0 Will be replaced to property hooks soon.
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
