<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions;

use Throwable;

use function sprintf;

class FailedToCreateSortClause extends SortByException {
    public function __construct(
        protected string $type,
        Throwable $previous = null,
    ) {
        parent::__construct(sprintf(
            'Impossible to create Sort Clause for `%s`.',
            $this->type,
        ), $previous);
    }

    public function getType(): string {
        return $this->type;
    }
}
