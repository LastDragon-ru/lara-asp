<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\Client;

use Throwable;

use function implode;
use function sprintf;

class SortClauseTooManyProperties extends SortLogicException {
    /**
     * @param array<string> $properties
     */
    public function __construct(
        protected array $properties,
        Throwable $previous = null,
    ) {
        parent::__construct(sprintf(
            'Only one property allowed, found: `%s`.',
            implode('`, `', $this->getProperties()),
        ), $previous);
    }

    /**
     * @return array<string>
     */
    public function getProperties(): array {
        return $this->properties;
    }
}
