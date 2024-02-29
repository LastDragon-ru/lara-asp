<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions;

use Throwable;

use function sprintf;

class TypeDefinitionIsNotScalar extends BuilderException {
    public function __construct(
        protected string $name,
        protected string $expected,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'The `%s` must be a scalar, `%s` given.',
                $this->name,
                $this->expected,
            ),
            $previous,
        );
    }

    public function getName(): string {
        return $this->name;
    }

    public function getExpected(): string {
        return $this->expected;
    }
}
