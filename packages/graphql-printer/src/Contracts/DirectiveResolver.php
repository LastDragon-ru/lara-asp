<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Contracts;

use GraphQL\Language\AST\DirectiveDefinitionNode;
use GraphQL\Type\Definition\Directive;

/**
 * GraphQL PHP doesn't standardize how the custom directives definitions should
 * be defined. The interface allows you to define your own way.
 *
 * @see https://webonyx.github.io/graphql-php/type-definitions/directives/
 * @see Printer::setDirectiveResolver()
 */
interface DirectiveResolver {
    public function getDefinition(string $name): DirectiveDefinitionNode|Directive|null;

    /**
     * @return array<DirectiveDefinitionNode|Directive>
     */
    public function getDefinitions(): array;
}
