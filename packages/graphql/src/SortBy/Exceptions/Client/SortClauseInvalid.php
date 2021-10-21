<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\Client;

use Throwable;

abstract class SortClauseInvalid extends SortLogicException {
    /**
     * @param array<mixed> $clause
     */
    public function __construct(
        protected int|string $index,
        protected array $clause,
        Throwable $previous = null,
    ) {
        parent::__construct($this->getReason(), $previous);
    }

    abstract protected function getReason(): string;

    public function getIndex(): int|string {
        return $this->index;
    }

    /**
     * @return array<mixed>
     */
    public function getClause(): array {
        return $this->clause;
    }
}
