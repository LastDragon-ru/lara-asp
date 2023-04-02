<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use Stringable;
use Throwable;

use function sprintf;

class TypeDefinitionImpossibleToCreateType extends BuilderException {
    /**
     * @param class-string<TypeDefinition> $definition
     */
    public function __construct(
        protected string $definition,
        protected Stringable|string|null $source,
        Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Definition `%s`: Impossible to create type for `%s`.',
                $this->definition,
                $this->source ?: 'null',
            ),
            $previous,
        );
    }

    public function getDefinition(): string {
        return $this->definition;
    }

    public function getSource(): Stringable|string|null {
        return $this->source;
    }
}
