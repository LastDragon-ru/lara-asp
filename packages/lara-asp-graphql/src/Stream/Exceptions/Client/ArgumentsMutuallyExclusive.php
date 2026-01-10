<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions\Client;

use Stringable;
use Throwable;

use function implode;
use function sprintf;

class ArgumentsMutuallyExclusive extends ClientException {
    /**
     * @param list<string> $arguments
     */
    public function __construct(
        protected Stringable|string $source,
        protected array $arguments,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'The arguments `%s` of `%s` are mutually exclusive.',
                implode('`, `', $this->arguments),
                $this->source,
            ),
            $previous,
        );
    }
}
