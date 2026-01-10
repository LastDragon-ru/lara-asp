<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Exceptions;

use SplFileInfo;
use Throwable;

use function sprintf;

class InvalidArgumentSplFileInfoIsNotAFile extends InvalidArgument {
    public function __construct(
        protected string $argument,
        protected SplFileInfo $value,
        ?Throwable $previous = null,
    ) {
        parent::__construct(sprintf(
            'Argument `%1$s` is not a file (path: `%2$s`).',
            $this->argument,
            $this->value->getPathname(),
        ), 0, $previous);
    }
}
