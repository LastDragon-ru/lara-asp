<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use Exception;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Core\Path\Path;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Metadata;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileMetadataError;
use Override;
use Stringable;

use function array_key_exists;
use function file_get_contents;
use function is_file;
use function is_writable;
use function sprintf;

class File implements Stringable {
    private ?string $content = null;
    private bool $modified   = false;

    /**
     * @var array<class-string<Metadata<mixed>>, mixed>
     */
    private array $metadata = [];

    public function __construct(
        private readonly FilePath $path,
        private readonly bool $writable,
    ) {
        if (!$this->path->isNormalized()) {
            throw new InvalidArgumentException(
                sprintf(
                    'Path must be normalized, `%s` given.',
                    $this->path,
                ),
            );
        }

        if (!$this->path->isAbsolute()) {
            throw new InvalidArgumentException(
                sprintf(
                    'Path must be absolute, `%s` given.',
                    $this->path,
                ),
            );
        }

        if (!is_file((string) $this->path)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The `%s` is not a file.',
                    $this->path,
                ),
            );
        }
    }

    public function getPath(): FilePath {
        return $this->path;
    }

    public function getName(): string {
        return $this->path->getName();
    }

    public function getExtension(): ?string {
        return $this->path->getExtension();
    }

    public function isWritable(): bool {
        return $this->writable && is_writable((string) $this->path);
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
                throw new FileMetadataError($this, $metadata, $exception);
            }
        }

        return $this->metadata[$metadata::class];
    }

    /**
     * @template T of Directory|self|Path
     *
     * @param T $path
     *
     * @return (T is Path ? new<T> : (T is Directory ? DirectoryPath : FilePath))
    */
    public function getRelativePath(Directory|self|Path $path): Path {
        $path = $path instanceof Path ? $path : $path->getPath();
        $path = $this->path->getRelativePath($path);

        return $path;
    }

    #[Override]
    public function __toString(): string {
        return (string) $this->path;
    }
}
