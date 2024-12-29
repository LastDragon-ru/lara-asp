<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Metadata;

use function file_get_contents;
use function is_file;
use function sprintf;

/**
 * @extends Item<FilePath>
 */
class File extends Item {
    private ?string $content  = null;
    private bool    $modified = false;

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

    public function isModified(): bool {
        return $this->modified;
    }

    public function getContent(): string {
        if ($this->content === null) {
            $this->content = (string) file_get_contents((string) $this->path);
        }

        return $this->content;
    }

    public function setContent(string $content): static {
        if ($this->content !== $content) {
            $this->content  = $content;
            $this->modified = true;

            if ($this->metadata instanceof MetadataStorage) {
                $this->metadata->reset($this);
            }
        }

        return $this;
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
