<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter;

use GraphQL\Type\Definition\Directive;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\ExecutableTypeNodeConverter;
use Nuwave\Lighthouse\Schema\Factories\DirectiveFactory;

class DirectiveResolver {
    protected DirectiveFactory $factory;

    public function __construct(
        protected DirectiveLocator $locator,
        protected ExecutableTypeNodeConverter $converter,
    ) {
        $this->factory = new DirectiveFactory($this->converter);
    }

    public function get(string $name): Directive {
        $definition = $this->locator->resolve($name)::definition();
        $directive  = $this->factory->handle(ASTHelper::extractDirectiveDefinition($definition));

        return $directive;
    }
}
