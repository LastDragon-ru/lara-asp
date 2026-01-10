<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client;

use Illuminate\Support\Arr;
use Throwable;

use function implode;
use function sprintf;

class ConditionTooManyOperators extends ClientException {
    /**
     * @param list<string> $operators
     */
    public function __construct(
        protected array $operators,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Only one operator allowed, found: `%s`.',
                implode('`, `', Arr::sort($this->getOperators())),
            ),
            $previous,
        );
    }

    /**
     * @return list<string>
     */
    public function getOperators(): array {
        return $this->operators;
    }
}
