<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc;

use GraphQL\Language\AST\DirectiveDefinitionNode;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\Directive as GraphQLDirective;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Exceptions\DirectiveDefinitionNotFound;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\ExecutableTypeNodeConverter;
use Nuwave\Lighthouse\Schema\Factories\DirectiveFactory;
use Nuwave\Lighthouse\Schema\TypeRegistry;
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
     * @var array<string,LighthouseDirective>
     */
    protected array $instances;

    /**
     * @var array<string,GraphQLDirective>
     */
    protected array $definitions;

    /**
     * @param array<GraphQLDirective> $directives
     */
    public function __construct(
        protected TypeRegistry $registry,
        protected DirectiveLocator $locator,
        protected ExecutableTypeNodeConverter $converter,
        array $directives = [],
    ) {
        $this->factory     = new DirectiveFactory($this->converter);
        $this->definitions = [];

        foreach ($directives as $directive) {
            $this->definitions[$directive->name] = $directive;
        }
    }

    public function getDefinition(string $name): GraphQLDirective {
        $directive = $this->definitions[$name] ?? null;

        if (!$directive) {
            // Definition can also contain types but seems these types are not
            // added to the Schema. So we need to add them (or we will get
            // "DefinitionException : Lighthouse failed while trying to load
            // a type XXX" error)
            $node     = null;
            $instance = $this->getInstance($name);
            $document = $instance instanceof LighthouseDirective
                ? Parser::parse($instance::definition())
                : null;

            foreach ($document?->definitions ?? [] as $definition) {
                if ($definition instanceof DirectiveDefinitionNode) {
                    $node = $definition;
                } elseif ($definition instanceof TypeDefinitionNode) {
                    $name = $definition->name->value;
                    $type = null;

                    if (!$this->registry->has($name)) {
                        if ($definition instanceof ScalarTypeDefinitionNode) {
                            // Lighthouse trying to load class for each scalar
                            // but some of them don't have `@scalar` and we will
                            // get "DefinitionException : Failed to find class
                            // extends GraphQL\Type\Definition\ScalarType" error.
                            // To avoid this we use a fake scalar.
                            //
                            // Maybe there is a better way?

                            $type = new CustomScalarType([
                                'name'      => $name,
                                'astNode'   => $definition,
                                'serialize' => static function (): mixed {
                                    return null;
                                },
                            ]);
                        } else {
                            $type = $this->registry->handle($definition);
                        }
                    }

                    if ($type) {
                        $this->registry->register($type);
                    }
                } else {
                    // empty
                }
            }

            if ($node) {
                $directive                = $this->factory->handle($node);
                $this->definitions[$name] = $directive;
            } else {
                throw new DirectiveDefinitionNotFound($name);
            }
        }

        return $directive;
    }

    public function getInstance(string $name): GraphQLDirective|LighthouseDirective {
        $directive = $this->instances[$name] ?? $this->definitions[$name] ?? null;

        if (!$directive) {
            $directive              = $this->locator->create($name);
            $this->instances[$name] = $directive;
        }

        return $directive;
    }

    /**
     * @return array<GraphQLDirective>
     */
    public function getDefinitions(): array {
        $directives = $this->definitions;

        foreach ($this->instances as $name => $instance) {
            $directives[$name] ??= $this->getDefinition($name);
        }

        return $directives;
    }
}
