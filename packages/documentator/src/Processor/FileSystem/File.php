<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use Exception;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Utils\Path;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Metadata;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileMetadataError;
use Override;
use Stringable;

use function array_key_exists;
use function dirname;
use function file_get_contents;
use function is_file;
use function is_writable;
use function pathinfo;
use function sprintf;

use const PATHINFO_BASENAME;
use const PATHINFO_EXTENSION;

class File implements Stringable {
    private ?string $content = null;
    private bool $modified   = false;

    /**
     * @var array<class-string<Metadata<mixed>>, mixed>
     */
    private array $metadata = [];

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

        if (!is_file($this->path)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The `%s` is not a file.',
                    $this->path,
                ),
            );
        }
    }

    public function getPath(): string {
        return $this->path;
    }

    public function getName(): string {
        return pathinfo($this->path, PATHINFO_BASENAME);
    }

    public function getExtension(): string {
        return pathinfo($this->path, PATHINFO_EXTENSION);
    }

    public function isWritable(): bool {
        return $this->writable && is_writable($this->path);
    }

    public function isModified(): bool {
        return $this->modified;
    }

    public function getContent(): string {
        if ($this->content === null) {
            $this->content = (string) file_get_contents($this->path);
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

    public function getRelativePath(Directory|self $root): string {
        $root = $root instanceof self ? dirname($root->getPath()) : $root->getPath();
        $path = Path::getRelativePath($root, $this->path);

        return $path;
    }

    #[Override]
    public function __toString(): string {
        return $this->getPath();
    }
}
