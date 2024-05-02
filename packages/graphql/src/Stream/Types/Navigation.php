<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Types;

use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Stream\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\Utils\TypeReference;
use Override;

class Navigation implements TypeDefinition {
    public function __construct() {
        // empty
    }

    #[Override]
    public function getTypeName(TypeSource $source, Context $context): string {
        return Directive::Name.'Navigation';
    }

    #[Override]
    public function getTypeDefinition(
        Manipulator $manipulator,
        TypeSource $source,
        Context $context,
        string $name,
    ): TypeDefinitionNode|TypeReference|null {
        $offset = $manipulator->getType(Offset::class, $source, $context);

        return Parser::objectTypeDefinition(
            <<<GRAPHQL
            type {$name} {
                previous: {$offset}
                current: {$offset}!
                next: {$offset}
            }
            GRAPHQL,
        );
    }
}
