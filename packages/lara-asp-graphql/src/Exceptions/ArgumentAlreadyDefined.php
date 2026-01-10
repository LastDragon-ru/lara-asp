<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Exceptions;

use Stringable;
use Throwable;

use function sprintf;

class ArgumentAlreadyDefined extends AstException {
    public function __construct(
        protected Stringable|string $source,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Argument `%s` already defined.',
                $this->source,
            ),
            $previous,
        );
    }

    public function getSource(): Stringable|string {
        return $this->source;
    }
}
