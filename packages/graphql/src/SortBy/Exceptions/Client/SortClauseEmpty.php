<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\Client;

use Throwable;

class SortClauseEmpty extends SortLogicException {
    public function __construct(Throwable $previous = null) {
        parent::__construct('Sort clause cannot be empty.', $previous);
    }
}
