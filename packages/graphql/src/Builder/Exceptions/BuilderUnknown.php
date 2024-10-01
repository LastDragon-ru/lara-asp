<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions;

use Stringable;
use Throwable;

use function sprintf;

class BuilderUnknown extends BuilderException {
    public function __construct(
        protected Stringable|string|null $source,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Impossible to determine builder type for `%s`.',
                $this->source ?? 'null',
            ),
            $previous,
        );
    }

    public function getSource(): Stringable|string|null {
        return $this->source;
    }
}
