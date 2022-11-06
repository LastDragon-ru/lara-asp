<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use Throwable;

use function sprintf;

class TypeDefinitionNoOperators extends SearchByException {
    /**
     * @param class-string<TypeDefinition> $definition
     */
    public function __construct(
        protected string $definition,
        protected string $type,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Type `%s`: List of operators for type `%s` cannot be empty.',
                $this->definition,
                $this->type,
            ),
            $previous,
        );
    }

    /**
     * @return class-string<TypeDefinition>
     */
    public function getDefinition(): string {
        return $this->definition;
    }

    public function getType(): string {
        return $this->type;
    }
}
