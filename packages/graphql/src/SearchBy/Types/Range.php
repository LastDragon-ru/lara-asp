<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Types;

use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;

class Range implements TypeDefinition {
    public function __construct() {
        // empty
    }

    public static function getName(): string {
        return 'Range';
    }

    public function getTypeDefinitionNode(
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
