<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions;

use Stringable;
use Throwable;

use function sprintf;

class FailedToCreateSearchCondition extends SearchByException {
    public function __construct(
        protected Stringable|string $source,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Impossible to create Search Condition for `%s`.',
                $this->source,
            ),
            $previous,
        );
    }

    public function getSource(): Stringable|string {
        return $this->source;
    }
}
