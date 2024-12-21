<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use Exception;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Metadata;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileMetadataUnresolvable;

use function array_key_exists;
use function file_get_contents;
use function is_file;
use function sprintf;

/**
 * @extends Item<FilePath>
 */
class File extends Item {
    private ?string $content  = null;
    private bool    $modified = false;

    /**
     * @var array<class-string<Metadata<mixed>>, mixed>
     */
    private array $metadata = [];

    public function __construct(FilePath $path) {
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
            $this->metadata = [];
        }

        return $this;
    }

    /**
     * @template T
     *
     * @param Metadata<T> $metadata
     *
     * @return T
     */
    public function getMetadata(Metadata $metadata): mixed {
        if (!array_key_exists($metadata::class, $this->metadata)) {
            try {
                $this->metadata[$metadata::class] = $metadata($this);
            } catch (Exception $exception) {
                throw new FileMetadataUnresolvable($this, $metadata, $exception);
            }
        }

        return $this->metadata[$metadata::class];
    }
}
