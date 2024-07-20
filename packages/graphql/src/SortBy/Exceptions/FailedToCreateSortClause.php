<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions;

use Stringable;
use Throwable;

use function sprintf;

class FailedToCreateSortClause extends SortByException {
    public function __construct(
        protected Stringable|string $source,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Impossible to create Sort Clause for `%s`.',
                $this->source,
            ),
            $previous,
        );
    }

    public function getSource(): Stringable|string {
        return $this->source;
    }
}
