<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions;

use Throwable;

use function sprintf;

class FailedToCreateSearchConditionForField extends SearchByException {
    public function __construct(
        protected string $type,
        protected string $field,
        Throwable $previous = null,
    ) {
        parent::__construct(sprintf(
            'Impossible to create Search Condition for field `%s` in `%s`.',
            $this->field,
            $this->type,
        ), $previous);
    }

    public function getType(): string {
        return $this->type;
    }

    public function getField(): string {
        return $this->field;
    }
}
