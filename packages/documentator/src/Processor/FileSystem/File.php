<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Utils\Path;
use Override;
use Stringable;
use WeakMap;

use function file_get_contents;
use function file_put_contents;
use function is_file;
use function is_writable;
use function pathinfo;
use function sprintf;

use const PATHINFO_BASENAME;
use const PATHINFO_EXTENSION;

class File implements Stringable {
    /**
     * @var ?WeakMap<object, mixed>
     */
    private ?WeakMap $context = null;
    private ?string  $content = null;

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

    public function getContent(): string {
        if ($this->content === null) {
            $this->content = (string) file_get_contents($this->path);
        }

        return $this->content;
    }

    public function setContent(string $content): static {
        if ($this->content !== $content) {
            $this->content = $content;
            $this->context = null;
        }

        return $this;
    }

    public function save(): bool {
        // Changed?
        if ($this->content === null) {
            return true;
        }

        // Save
        return $this->isWritable()
            && file_put_contents($this->path, $this->content) !== false;
    }

    public function getContext(object $object): mixed {
        return $this->context[$object] ?? null;
    }

    public function setContext(object $object, mixed $value): static {
        $map           = $this->context ?? new WeakMap();
        $map[$object]  = $value;
        $this->context = $map;

        return $this;
    }

    public function getRelativePath(Directory $root): string {
        return Path::getRelativePath($root->getPath(), $this->path);
    }

    #[Override]
    public function __toString(): string {
        return $this->getPath();
    }
}
