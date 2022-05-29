<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions;

use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Builder;
use Throwable;

use function sprintf;

class BuilderInvalidConditions extends SearchByException {
    public function __construct(
        protected Builder $builder,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Conditions for `%s` are invalid.',
                $this->getBuilder()::class,
            ),
            $previous,
        );
    }

    public function getBuilder(): Builder {
        return $this->builder;
    }
}
