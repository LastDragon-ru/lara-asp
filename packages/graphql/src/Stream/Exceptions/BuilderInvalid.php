<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions;

use Stringable;
use Throwable;

use function sprintf;

class BuilderInvalid extends StreamException {
    public function __construct(
        protected Stringable|string $source,
        protected string $type,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'The builder must be an object instance, `%s` given (`%s`).',
                $this->type,
                $this->source,
            ),
            $previous,
        );
    }

    public function getSource(): Stringable|string {
        return $this->source;
    }

    public function getType(): string {
        return $this->type;
    }
}
