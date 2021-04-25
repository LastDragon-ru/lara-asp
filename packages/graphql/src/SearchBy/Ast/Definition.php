<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast;

use GraphQL\Language\AST\TypeDefinitionNode;
use Illuminate\Contracts\Container\Container;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeDefinition;

use function is_null;

class Definition implements TypeDefinition {
    protected TypeDefinition|null $instance = null;

    /**
     * @param class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeDefinition> $definition
     */
    public function __construct(
        protected Container $container,
        protected string $definition,
    ) {
        // empty
    }

    /**
     * @return class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeDefinition>
     */
    public function getDefinition(): string {
        return $this->definition;
    }

    protected function getInstance(): TypeDefinition {
        if (is_null($this->instance)) {
            $this->instance = $this->container->make($this->getDefinition());
        }

        return $this->instance;
    }

    public function get(string $name, string $scalar = null, bool $nullable = null): ?TypeDefinitionNode {
        return $this->getInstance()->get($name, $scalar, $nullable);
    }
}
