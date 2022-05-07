<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Utils;

use Stringable;

use function array_slice;
use function end;
use function implode;

class Property implements Stringable {
    /**
     * @var array
     */
    protected array $path;

    /**
     * @param string ...$path
     */
    public function __construct(
        string ...$path,
    ) {
        $this->path = $path;
    }

    public function getName(): string {
        return end($this->path) ?: '';
    }

    /**
     * @return array<int, string>
     */
    public function getPath(): array {
        return $this->path;
    }

    public function getChild(string $name): Property {
        return new Property(...[...$this->path, $name]);
    }

    public function getParent(): ?Property {
        $path   = array_slice($this->path, -1);
        $parent = $path ? new Property(...$path) : null;

        return $parent;
    }

    public function __toString(): string {
        return implode('.', $this->path);
    }
}
