<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Types;

use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeDefinition;

use function is_null;

class Flag implements TypeDefinition {
    public const Name = 'Flag';

    public function get(string $name, string $scalar = null, bool $nullable = null): ?TypeDefinitionNode {
        $type = null;

        if (is_null($scalar) && is_null($nullable)) {
            $type = Parser::enumTypeDefinition(
                /** @lang GraphQL */
                <<<GRAPHQL
                enum {$name} {
                    yes
                }
                GRAPHQL,
            );
        }

        return $type;
    }
}
