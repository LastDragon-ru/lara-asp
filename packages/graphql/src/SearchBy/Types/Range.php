<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Types;

use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeDefinition;

use function is_null;

class Range implements TypeDefinition {
    public static function getName(): string {
        return 'Range';
    }

    public function getTypeDefinitionNode(
        string $name,
        string $scalar = null,
        bool $nullable = null,
    ): ?TypeDefinitionNode {
        $type = null;

        if ($scalar && is_null($nullable)) {
            $type = Parser::inputObjectTypeDefinition(
                /** @lang GraphQL */
                <<<GRAPHQL
                input {$name} {
                    min: {$scalar}!
                    max: {$scalar}!
                }
                GRAPHQL,
            );
        }

        return $type;
    }
}
