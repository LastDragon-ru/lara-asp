<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Exceptions;

use Throwable;

use function sprintf;

class TypeNotFound extends Exception {
    public function __construct(
        protected string $type,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Type `%s` not found in the Schema.',
                $this->type,
            ),
            $previous,
        );
    }

    public function getType(): string {
        return $this->type;
    }
}
