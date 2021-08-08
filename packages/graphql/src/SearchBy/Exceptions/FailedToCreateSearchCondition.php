<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions;

use Throwable;

use function sprintf;

class FailedToCreateSearchCondition extends SearchByException {
    public function __construct(
        protected string $type,
        Throwable $previous = null,
    ) {
        parent::__construct(sprintf(
            'Impossible to create Search Condition for `%s`.',
            $this->type,
        ), $previous);
    }

    public function getType(): string {
        return $this->type;
    }
}
