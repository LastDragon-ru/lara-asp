<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions;

use Throwable;

class ComplexOperatorNotFound extends SearchByException {
    public function __construct(
        Throwable $previous = null,
    ) {
        parent::__construct('Complex operator not found.', $previous);
    }
}
