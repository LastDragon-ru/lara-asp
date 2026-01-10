<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Exceptions;

use DOMDocument;
use Throwable;

use function sprintf;

class InvalidArgumentXml extends InvalidArgument {
    public function __construct(
        protected string $argument,
        protected mixed $value,
        ?Throwable $previous = null,
    ) {
        parent::__construct(sprintf(
            'Argument `%1$s` must be instance of `%2$s` or a valid XML string.',
            $this->argument,
            DOMDocument::class,
        ), 0, $previous);
    }
}
