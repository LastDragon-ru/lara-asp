<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Metadata;

use function is_file;
use function sprintf;

/**
 * @extends Entry<FilePath>
 */
class File extends Entry {
    public function __construct(
        protected readonly MetadataResolver $metadata,
        FilePath $path,
    ) {
        parent::__construct($path);

        if (!is_file((string) $this->path)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The `%s` is not a file.',
                    $this->path,
                ),
            );
        }
    }

    public function getExtension(): ?string {
        return $this->path->getExtension();
    }

    /**
     * @template T
     *
     * @param class-string<Metadata<T>> $metadata
     *
     * @return T
     */
    public function getMetadata(string $metadata): mixed {
        return $this->metadata->get($this, $metadata);
    }
}
