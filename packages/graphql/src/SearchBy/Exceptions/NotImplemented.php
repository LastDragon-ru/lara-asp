<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions;

use Throwable;

use function sprintf;

class NotImplemented extends SearchByException {
    public function __construct(
        protected string $feature,
        Throwable $previous = null,
    ) {
        parent::__construct(sprintf(
            'Hmm... Seems `%s` not yet supported :( Please contact to developer.',
            $feature,
        ), $previous);
    }

    public function getFeature(): string {
        return $this->feature;
    }
}
