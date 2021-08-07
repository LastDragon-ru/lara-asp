<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\Client;

use Throwable;

class SearchConditionEmpty extends SearchLogicException {
    public function __construct(Throwable $previous = null) {
        parent::__construct('Search condition cannot be empty.', $previous);
    }
}
