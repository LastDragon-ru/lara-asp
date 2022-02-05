<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc;

use GraphQL\Type\Definition\Directive as GraphQLDirective;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\ExecutableTypeNodeConverter;
use Nuwave\Lighthouse\Schema\Factories\DirectiveFactory;
use Nuwave\Lighthouse\Support\Contracts\Directive as LighthouseDirective;

/**
 * Class helps us to search defined directives and convert AST nodes into
 * `Directive` instances.
 *
 * This is required because despite GraphQL-PHP supports custom directives it
 * doesn't allow to add them into Types and after parsing the scheme they will
 * be available only inside `astNode` as an array of `DirectiveDefinitionNode`.
 * On another hand, Lighthouse uses its own Directive Locator to associate
 * directives with classes.
 *
 * @see https://webonyx.github.io/graphql-php/type-definitions/directives/
 *
 * @internal
 */
class DirectiveResolver {
    protected DirectiveFactory $factory;

    /**
     * @var array<string,GraphQLDirective>
     */
    protected array $directives;

    /**
     * @param array<GraphQLDirective> $directives
     */
    public function __construct(
        protected DirectiveLocator $locator,
        protected ExecutableTypeNodeConverter $converter,
        array $directives = [],
    ) {
        $this->factory    = new DirectiveFactory($this->converter);
        $this->directives = [];

        foreach ($directives as $directive) {
            $this->directives[$directive->name] = $directive;
        }
    }

    public function getDefinition(string $name): GraphQLDirective {
        $directive = $this->directives[$name] ?? null;

        if (!$directive) {
            $definition = $this->locator->resolve($name)::definition();
            $definition = ASTHelper::extractDirectiveDefinition($definition);
            $directive  = $this->factory->handle($definition);
        }

        return $directive;
    }

    public function getInstance(string $name): GraphQLDirective|LighthouseDirective {
        return $this->directives[$name] ?? $this->locator->create($name);
    }

    /**
     * @return array<GraphQLDirective>
     */
    public function getDefinitions(): array {
        $directives = $this->directives;

        foreach ($this->locator->definitions() as $definition) {
            $directive                    = $this->factory->handle($definition);
            $directives[$directive->name] = $directive;
        }

        return $directives;
    }
}
