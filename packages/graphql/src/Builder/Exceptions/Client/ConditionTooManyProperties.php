<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client;

use Throwable;

use function implode;
use function sprintf;

class ConditionTooManyProperties extends ClientException {
    /**
     * @param list<string> $properties
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
     * @return list<string>
     */
    public function getProperties(): array {
        return $this->properties;
    }
}
