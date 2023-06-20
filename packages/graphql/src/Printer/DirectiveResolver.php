<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Printer;

use GraphQL\Language\AST\DirectiveDefinitionNode;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\Directive as GraphQLDirective;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\DirectiveResolver as DirectiveResolverContract;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\TypeRegistry;

use function array_key_exists;

/**
 * Class helps us to search available directives.
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
class DirectiveResolver implements DirectiveResolverContract {
    /**
     * @var array<string, DirectiveDefinitionNode|GraphQLDirective|null>
     */
    protected array $definitions = [];

    public function __construct(
        protected TypeRegistry $registry,
        protected DirectiveLocator $locator,
    ) {
        // empty
    }

    public function getDefinition(string $name): DirectiveDefinitionNode|GraphQLDirective|null {
        if (!array_key_exists($name, $this->definitions)) {
            // Definition can also contain types but seems these types are not
            // added to the Schema. So we need to add them (or we will get
            // "DefinitionException : Lighthouse failed while trying to load
            // a type XXX" error)
            $directive = null;
            $instance  = $this->locator->resolve($name);
            $document  = Parser::parse($instance::definition());

            foreach ($document->definitions as $definition) {
                if ($definition instanceof DirectiveDefinitionNode) {
                    $directive = $definition;
                } elseif ($definition instanceof TypeDefinitionNode) {
                    $name = $definition->getName()->value;
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

            // Cache
            $this->definitions[$name] = $directive;
        }

        return $this->definitions[$name] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getDefinitions(): array {
        return [];
    }
}
