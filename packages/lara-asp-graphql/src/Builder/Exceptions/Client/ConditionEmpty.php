<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client;

use Throwable;

class ConditionEmpty extends ClientException {
    public function __construct(?Throwable $previous = null) {
        parent::__construct('Condition cannot be empty.', $previous);
    }
}
