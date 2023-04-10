<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Exceptions;

use Stringable;
use Throwable;

use function sprintf;

class TypeUnexpected extends AstException {
    public function __construct(
        protected Stringable|string $source,
        protected Stringable|string $expected,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Type `%s` is not a `%s`.',
                $this->source,
                $this->expected,
            ),
            $previous,
        );
    }

    public function getSource(): Stringable|string {
        return $this->source;
    }

    public function getExpected(): Stringable|string {
        return $this->expected;
    }
}
