<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Exceptions;

use LastDragon_ru\LaraASP\GraphQLPrinter\PackageException;
use Throwable;

use function sprintf;

class DirectiveArgumentNotFound extends PackageException {
    public function __construct(
        protected string $directive,
        protected string $argument,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Argument `%s(%s)` not found in the Schema.',
                $this->directive,
                $this->argument,
            ),
            $previous,
        );
    }

    public function getDirective(): string {
        return $this->directive;
    }

    public function getArgument(): string {
        return $this->argument;
    }
}
