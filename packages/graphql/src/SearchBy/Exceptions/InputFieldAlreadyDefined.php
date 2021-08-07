<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions;

use Throwable;

use function sprintf;

class InputFieldAlreadyDefined extends SearchByException {
    public function __construct(
        protected string $name,
        Throwable $previous = null,
    ) {
        parent::__construct(sprintf(
            'Input field `%s` already defined.',
            $this->name,
        ), $previous);
    }

    public function getName(): string {
        return $this->name;
    }
}
