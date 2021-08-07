<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions;

use Throwable;

use function sprintf;

class EnumNoOperators extends SearchByException {
    public function __construct(
        protected string $enum,
        Throwable $previous = null,
    ) {
        parent::__construct(sprintf(
            'List of operators for enum `%s` cannot be empty.',
            $this->enum,
        ), $previous);
    }

    public function getEnum(): string {
        return $this->enum;
    }
}
