<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Database;

use Illuminate\Database\Eloquent\Builder;

use function array_column;
use function array_filter;
use function array_pop;
use function end;
use function implode;

class SortStack {
    /**
     * @var array<array{string,Builder}>
     */
    protected array $stack = [];
    /**
     * @var array<string,string>
     */
    protected array $joins = [];

    public function __construct(
        protected Builder $builder,
    ) {
        // empty
    }

    public function push(string $relation, Builder $builder): static {
        $this->stack[] = [$relation, $builder];

        return $this;
    }

    public function pop(): void {
        array_pop($this->stack);
    }

    public function hasTableAlias(): bool {
        return isset($this->joins[$this->path()]);
    }

    public function getTableAlias(): ?string {
        return $this->joins[$this->path()] ?? null;
    }

    public function setTableAlias(string $alias): static {
        $this->joins[$this->path()] = $alias;

        return $this;
    }

    public function getBuilder(): Builder {
        return ((array) end($this->stack) + ['', null])[1] ?? $this->builder;
    }

    /**
     * @return array<string>
     */
    public function getPath(): array {
        return array_filter(array_column($this->stack, 0));
    }

    protected function path(): string {
        return implode('.', $this->getPath());
    }
}
