<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Builders;

class Clause {
    /**
     * @param non-empty-array<string> $path
     */
    public function __construct(
        protected array $path,
        protected ?string $direction,
    ) {
        // empty
    }

    /**
     * @return non-empty-array<string>
     */
    public function getPath(): array {
        return $this->path;
    }

    public function getDirection(): ?string {
        return $this->direction;
    }
}
