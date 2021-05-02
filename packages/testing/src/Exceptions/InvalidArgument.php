<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Exceptions;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Testing\PackageException;

use function gettype;
use function is_object;

abstract class InvalidArgument extends InvalidArgumentException implements PackageException {
    protected function getType(mixed $value): string {
        return is_object($value) ? $value::class : gettype($value);
    }
}
