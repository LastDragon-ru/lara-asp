<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use LastDragon_ru\Path\FilePath;
use League\CommonMark\Node\Block\Document as DocumentNode;
use OutOfBoundsException;

use function assert;

/**
 * Temporary solution to avoid adding getter/setter methods until PHP 8.3 support drop.
 *
 * @see https://phpstan.org/blog/solving-phpstan-access-to-undefined-property
 *
 * @property-read DocumentNode $node
 * @property ?FilePath         $path
 */
abstract class DocumentImpl {
    public function __isset(string $name): bool {
        return match ($name) {
            'path'  => $this->getPath() !== null,
            'node'  => true,
            default => false,
        };
    }

    public function __get(string $name): mixed {
        return match ($name) {
            'path'  => $this->getPath(),
            'node'  => $this->getNode(),
            default => throw new OutOfBoundsException("Property {$name} does not exist."),
        };
    }

    public function __set(string $name, mixed $value): void {
        if ($name === 'path') {
            assert($value === null || $value instanceof FilePath);

            $this->setPath($value);
        } else {
            throw new OutOfBoundsException("Property {$name} does not exist.");
        }
    }

    abstract protected function getPath(): ?FilePath;

    abstract protected function setPath(?FilePath $path): void;

    abstract protected function getNode(): DocumentNode;
}
