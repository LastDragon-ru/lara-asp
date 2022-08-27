<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Types;

use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;

class Flag implements TypeDefinition {
    public function __construct() {
        // empty
    }

    public static function getName(): string {
        return 'Flag';
    }

    public function getTypeDefinitionNode(
        string $name,
        string $type = null,
        bool $nullable = null,
    ): ?TypeDefinitionNode {
        $node = null;

        if ($type === null && $nullable === null) {
            $node = Parser::enumTypeDefinition(
                /** @lang GraphQL */
                <<<GRAPHQL
                enum {$name} {
                    yes
                }
                GRAPHQL,
            );
        }

        return $node;
    }
}
