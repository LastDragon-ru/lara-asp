<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\Client;

use function sprintf;

class SortClauseEmpty extends SortClauseInvalid {
    protected function getReason(): string {
        return sprintf(
            'Sort Clause `%s`: Sort clause cannot be empty.',
            $this->getIndex(),
        );
    }
}
