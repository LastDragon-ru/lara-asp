<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Exceptions;

use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Support\Contracts\Directive;
use Stringable;
use Throwable;

use function sprintf;

class ArgumentMissed extends StreamException {
    /**
     * @param class-string<Directive> $directive
     */
    public function __construct(
        protected Stringable|string $source,
        protected string $directive,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'The `%s` must have at least one argument marked by `%s` directive.',
                $this->source,
                '@'.DirectiveLocator::directiveName($this->directive),
            ),
            $previous,
        );
    }

    public function getSource(): Stringable|string {
        return $this->source;
    }

    /**
     * @return class-string<Directive>
     */
    public function getDirective(): string {
        return $this->directive;
    }
}
