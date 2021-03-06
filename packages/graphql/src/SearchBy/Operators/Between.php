<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\OperatorHasTypesForScalar;

class Between extends BaseOperator implements OperatorHasTypesForScalar {
    public function getName(): string {
        return 'between';
    }

    protected function getDescription(): string {
        return 'Within a range.';
    }

    /**
     * @inheritdoc
     */
    public function getTypeDefinitionsForScalar(string $name, string $type): array {
        return [
            Parser::inputObjectTypeDefinition(
                /** @lang GraphQL */
                <<<GRAPHQL
                input {$name} {
                    min: {$type}!
                    max: {$type}!
                }
                GRAPHQL,
            ),
        ];
    }
}
