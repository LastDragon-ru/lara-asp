<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Exceptions;

use LastDragon_ru\LaraASP\GraphQL\PackageException;
use Stringable;
use Throwable;

use function sprintf;

class NotImplemented extends PackageException {
    public function __construct(
        protected Stringable|string $feature,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Hmm... Seems `%s` not yet supported 🤷 You are free to create an issue/pull request.',
                $feature,
            ),
            $previous,
        );
    }

    public function getFeature(): Stringable|string {
        return $this->feature;
    }
}
