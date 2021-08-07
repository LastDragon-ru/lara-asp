<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions;

use Throwable;

use function sprintf;

class BuilderUnsupported extends SearchByException {
    /**
     * @param class-string $builder
     */
    public function __construct(
        protected string $builder,
        Throwable $previous = null,
    ) {
        parent::__construct(sprintf(
            'Relation cannot be used with `%s`.',
            $this->builder,
        ), $previous);
    }

    /**
     * @return class-string
     */
    public function getBuilder(): string {
        return $this->builder;
    }
}
