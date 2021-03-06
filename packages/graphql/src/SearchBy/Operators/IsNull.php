<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\OperatorHasTypes;

class IsNull extends BaseOperator implements OperatorHasTypes {
    public function getName(): string {
        return 'isNull';
    }

    protected function getDescription(): string {
        return 'IS NULL (value of property not matter)';
    }

    /**
     * @inheritdoc
     */
    public function getTypeDefinitions(string $name): array {
        return [
            Parser::enumTypeDefinition(
                /** @lang GraphQL */
                <<<GRAPHQL
                enum {$name} {
                    YES
                }
                GRAPHQL,
            ),
        ];
    }
}
