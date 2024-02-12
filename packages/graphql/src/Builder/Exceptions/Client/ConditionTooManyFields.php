<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client;

use Illuminate\Support\Arr;
use Throwable;

use function implode;
use function sprintf;

class ConditionTooManyFields extends ClientException {
    /**
     * @param list<string> $fields
     */
    public function __construct(
        protected readonly array $fields,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Only one field allowed, found: `%s`.',
                implode('`, `', Arr::sort($this->getFields())),
            ),
            $previous,
        );
    }

    /**
     * @return list<string>
     */
    public function getFields(): array {
        return $this->fields;
    }
}
