<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions;

use Stringable;
use Throwable;

use function sprintf;

class BuilderUnsupported extends StreamException {
    /**
     * @param class-string $builder
     */
    public function __construct(
        protected Stringable|string $source,
        protected string $builder,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'The `%s` builder is not supported (`%s`).',
                $this->builder,
                $this->source,
            ),
            $previous,
        );
    }

    public function getSource(): Stringable|string {
        return $this->source;
    }

    /**
     * @return class-string
     */
    public function getBuilder(): string {
        return $this->builder;
    }
}
