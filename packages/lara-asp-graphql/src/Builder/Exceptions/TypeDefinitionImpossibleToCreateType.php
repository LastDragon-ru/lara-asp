<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
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
        protected Stringable|string $source,
        protected Context $context,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'Definition `%s`: Impossible to create type for `%s`.',
                $this->definition,
                $this->source,
            ),
            $previous,
        );
    }

    public function getDefinition(): string {
        return $this->definition;
    }

    public function getSource(): Stringable|string {
        return $this->source;
    }

    public function getContext(): Context {
        return $this->context;
    }
}
