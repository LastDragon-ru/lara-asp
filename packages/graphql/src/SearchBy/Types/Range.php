<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Types;

use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\GraphQL\Builder\BuilderInfo;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;

class Range implements TypeDefinition {
    public function __construct() {
        // empty
    }

    public static function getName(BuilderInfo $builder, string $type = null, bool $nullable = null): string {
        return Directive::Name.'TypeRange'.((string) $type);
    }

    public function getTypeDefinitionNode(
        Manipulator $manipulator,
        string $name,
        string $type = null,
        bool $nullable = null,
    ): ?TypeDefinitionNode {
        $node = null;

        if ($type && $nullable === null) {
            $node = Parser::inputObjectTypeDefinition(
            /** @lang GraphQL */
                <<<GRAPHQL
                input {$name} {
                    min: {$type}!
                    max: {$type}!
                }
                GRAPHQL,
            );
        }

        return $node;
    }
}
