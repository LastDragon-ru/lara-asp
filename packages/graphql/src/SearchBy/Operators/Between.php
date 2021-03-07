<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\OperatorHasTypesForScalar;

class Between extends BaseOperator implements OperatorHasTypesForScalar {
    protected const TYPE_RANGE = 'Range';

    public function getName(): string {
        return 'between';
    }

    protected function getDescription(): string {
        return 'Within a range.';
    }

    /**
     * @inheritdoc
     */
    public function getDefinition(array $map, string $scalar, bool $nullable): string {
        return parent::getDefinition($map, $map[self::TYPE_RANGE], true);
    }

    /**
     * @inheritdoc
     */
    public function getTypeDefinitionsForScalar(string $prefix, string $scalar): array {
        return [
            self::TYPE_RANGE => Parser::inputObjectTypeDefinition(
                /** @lang GraphQL */
                <<<GRAPHQL
                input {$prefix} {
                    min: {$scalar}!
                    max: {$scalar}!
                }
                GRAPHQL,
            ),
        ];
    }
}
