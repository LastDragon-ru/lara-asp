<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Exceptions;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Testing\PackageException;
use SplFileInfo;
use Throwable;

use function sprintf;

class InvalidArgumentSplFileInfoIsNotReadable extends InvalidArgumentException implements PackageException {
    public function __construct(
        protected string $argument,
        protected SplFileInfo $value,
        Throwable $previous = null,
    ) {
        parent::__construct(sprintf(
            'Argument `%1$s` is file but not readable (path: `%2$s`).',
            $this->argument,
            $this->value->getPathname(),
        ), 0, $previous);
    }
}
