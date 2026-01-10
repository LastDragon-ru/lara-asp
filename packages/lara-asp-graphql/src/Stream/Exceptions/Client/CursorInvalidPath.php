<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions\Client;

use Throwable;

use function sprintf;

class CursorInvalidPath extends ClientException {
    public function __construct(
        protected string $expected,
        protected string $actual,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'The cursor belonging to `%s` field expected, `%s` given.',
                $this->expected,
                $this->actual,
            ),
            $previous,
        );
    }
}
