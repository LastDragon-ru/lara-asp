<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions;

use Throwable;

use function sprintf;

class DefinitionUnknown extends SearchByException {
    public function __construct(
        protected string $name,
        Throwable $previous = null,
    ) {
        parent::__construct(sprintf(
            'Definition `%s` is not defined.',
            $this->name,
        ), $previous);
    }

    public function getName(): string {
        return $this->name;
    }
}
