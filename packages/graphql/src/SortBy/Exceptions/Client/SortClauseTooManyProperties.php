<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\Client;

use function sprintf;

class SortClauseTooManyProperties extends SortClauseInvalid {
    protected function getReason(): string {
        return sprintf(
            'Sort Clause `%s`: Only one property allowed.',
            $this->getIndex(),
        );
    }
}
