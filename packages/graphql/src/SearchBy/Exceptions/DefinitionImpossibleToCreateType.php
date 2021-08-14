<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions;

use Throwable;

use function sprintf;

class DefinitionImpossibleToCreateType extends SearchByException {
    public function __construct(
        protected string $name,
        protected ?string $scalar,
        protected ?bool $nullable,
        Throwable $previous = null,
    ) {
        parent::__construct(sprintf(
            'Definition `%s`: Impossible to create type for scalar `%s`.',
            $this->name,
            ($this->scalar ?: 'null').($this->nullable ? '' : '!'),
        ), $previous);
    }

    public function getName(): string {
        return $this->name;
    }

    public function getScalar(): ?string {
        return $this->scalar;
    }

    public function isNullable(): ?bool {
        return $this->nullable;
    }
}
