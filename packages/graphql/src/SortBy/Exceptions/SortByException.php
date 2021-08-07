<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions;

use LastDragon_ru\LaraASP\GraphQL\PackageException;
use Throwable;

abstract class SortByException extends PackageException {
    public function __construct(string $message = '', Throwable $previous = null) {
        parent::__construct($message, 0, $previous);
    }
}
