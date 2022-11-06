<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use Throwable;

use function sprintf;

class TypeDefinitionNoTypeName extends SearchByException {
    /**
     * @param class-string<TypeDefinition> $definition
     */
    public function __construct(
        protected string $definition,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Type `%s`: Type name cannot be `null`.',
                $this->definition,
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
}
