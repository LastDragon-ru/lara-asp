<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Exceptions;

use LastDragon_ru\GraphQLPrinter\PackageException;
use Throwable;

use function sprintf;

class FieldNotFound extends PackageException {
    public function __construct(
        protected string $type,
        protected string $field,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Field `%s { %s }` not found in the Schema.',
                $this->type,
                $this->field,
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
}
