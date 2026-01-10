<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Database\QueryLog;

class Query {
    /**
     * @param array<array-key, mixed> $bindings
     */
    public function __construct(
        protected string $query,
        protected array $bindings = [],
    ) {
        // empty
    }

    public function getQuery(): string {
        return $this->query;
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getBindings(): array {
        return $this->bindings;
    }
}
