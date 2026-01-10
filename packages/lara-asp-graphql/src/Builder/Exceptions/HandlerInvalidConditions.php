<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use Throwable;

use function sprintf;

class HandlerInvalidConditions extends BuilderException {
    public function __construct(
        protected Handler $handler,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Conditions for `%s` are invalid.',
                $this->getHandler()::class,
            ),
            $previous,
        );
    }

    public function getHandler(): Handler {
        return $this->handler;
    }
}
