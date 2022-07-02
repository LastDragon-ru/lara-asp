<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Types;

use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;

use function is_null;

class Direction implements TypeDefinition {
    public function __construct() {
        // empty
    }

    public static function getName(): string {
        return 'Direction';
    }

    public function getTypeDefinitionNode(
        string $name,
        string $scalar = null,
        bool $nullable = null,
    ): ?TypeDefinitionNode {
        $type = null;

        if (is_null($scalar) && is_null($nullable)) {
            $type = Parser::enumTypeDefinition(
            /** @lang GraphQL */
                <<<GRAPHQL
                """
                Sort direction.
                """
                enum {$name} {
                    asc
                    desc
                }
                GRAPHQL,
            );
        }

        return $type;
    }
}
