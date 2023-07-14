<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Exceptions;

use LastDragon_ru\LaraASP\GraphQLPrinter\PackageException;
use Throwable;

use function sprintf;

class FieldArgumentNotFound extends PackageException {
    public function __construct(
        protected string $type,
        protected string $field,
        protected string $argument,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Argument `%s { %s(%s) }` not found in the Schema.',
                $this->type,
                $this->field,
                $this->argument,
            ),
            $previous,
        );
    }

    public function getType(): string {
        return $this->type;
    }

    public function getField(): string {
        return $this->field;
    }

    public function getArgument(): string {
        return $this->argument;
    }
}
