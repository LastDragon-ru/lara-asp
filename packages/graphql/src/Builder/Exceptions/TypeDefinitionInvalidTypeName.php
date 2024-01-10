<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use Throwable;

use function sprintf;

class TypeDefinitionInvalidTypeName extends BuilderException {
    /**
     * @param class-string<TypeDefinition> $type
     */
    public function __construct(
        protected string $type,
        protected string $expected,
        protected string $actual,
        protected Context $context,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Generated type for TypeDefinition `%s` must be named as `%s`, but its name is `%s`.',
                $this->type,
                $this->expected,
                $this->actual,
            ),
            $previous,
        );
    }

    /**
     * @return class-string<TypeDefinition>
     */
    public function getType(): string {
        return $this->type;
    }

    public function getExpected(): string {
        return $this->expected;
    }

    public function getActual(): string {
        return $this->actual;
    }

    public function getContext(): Context {
        return $this->context;
    }
}
