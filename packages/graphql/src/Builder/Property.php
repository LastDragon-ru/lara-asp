<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder;

use Stringable;

use function array_slice;
use function end;
use function implode;

class Property implements Stringable {
    /**
     * @var array<int, string>
     */
    protected array $path;

    final public function __construct(
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

    public function getChild(string $name): static {
        return new static(...[...$this->path, $name]);
    }

    public function getParent(): static {
        $path   = array_slice($this->path, 0, -1);
        $parent = new static(...$path);

        return $parent;
    }

    public function __toString(): string {
        return implode('.', $this->path);
    }
}
