<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions;

use Throwable;

use function sprintf;

class ScalarNoOperators extends BuilderException {
    public function __construct(
        protected string $name,
        Throwable $previous = null,
    ) {
        parent::__construct(sprintf(
            'List of operators for scalar `%s` cannot be empty.',
            $this->name,
        ), $previous);
    }

    public function getName(): string {
        return $this->name;
    }
}
