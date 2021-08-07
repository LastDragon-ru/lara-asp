<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\Client\SortClauseEmpty;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\Client\SortClauseTooManyProperties;

use function array_keys;
use function count;
use function is_array;
use function is_string;
use function key;
use function reset;

class SortClause {
    protected string  $column;
    protected ?string $direction = null;
    /**
     * @var array<string,mixed>|null
     */
    protected ?array $child = null;

    /**
     * @param array<string,mixed> $clause
     */
    public function __construct(array $clause) {
        // Empty?
        if (!$clause) {
            throw new SortClauseEmpty();
        }

        // More than one property?
        if (count($clause) > 1) {
            throw new SortClauseTooManyProperties(array_keys($clause));
        }

        // Parse
        $direction       = reset($clause);
        $this->column    = key($clause);
        $this->child     = is_array($direction) ? $direction : null;
        $this->direction = is_string($direction) ? $direction : null;
    }

    public function getColumn(): string {
        return $this->column;
    }

    public function getDirection(): ?string {
        return $this->direction;
    }

    /**
     * @return array<string,mixed>|null
     */
    public function getChild(): ?array {
        return $this->child;
    }

    /**
     * @return array<string,mixed>|null
     */
    public function getChildClause(): ?static {
        return $this->isRelation()
            ? new static($this->getChild())
            : null;
    }

    public function isColumn(): bool {
        return $this->getDirection() !== null;
    }

    public function isRelation(): bool {
        return !$this->isColumn();
    }
}
