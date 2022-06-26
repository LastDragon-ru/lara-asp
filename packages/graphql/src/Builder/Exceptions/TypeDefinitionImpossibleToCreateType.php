<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use Throwable;

use function sprintf;

class TypeDefinitionImpossibleToCreateType extends BuilderException {
    /**
     * @param class-string<TypeDefinition> $definition
     */
    public function __construct(
        protected string $definition,
        protected ?string $scalar,
        protected ?bool $nullable,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Definition `%s`: Impossible to create type for scalar `%s`.',
                $this->definition,
                ($this->scalar ?: 'null').($this->nullable ? '' : '!'),
            ),
            $previous,
        );
    }

    public function getDefinition(): string {
        return $this->definition;
    }

    public function getScalar(): ?string {
        return $this->scalar;
    }

    public function isNullable(): ?bool {
        return $this->nullable;
    }
}
