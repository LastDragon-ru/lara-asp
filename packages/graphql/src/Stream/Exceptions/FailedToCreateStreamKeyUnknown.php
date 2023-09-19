<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions;

use Stringable;
use Throwable;

use function sprintf;

class FailedToCreateStreamKeyUnknown extends StreamException {
    public function __construct(
        protected Stringable|string $source,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Impossible to create a stream for `%s` because the `key` is unknown.',
                $this->source,
            ),
            $previous,
        );
    }

    public function getSource(): Stringable|string {
        return $this->source;
    }
}
