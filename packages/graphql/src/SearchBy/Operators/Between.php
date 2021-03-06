<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\OperatorHasType;

class Between extends Operator implements OperatorHasType {
    public function getName(): string {
        return 'between';
    }

    public function getDescription(): string {
        return 'Within a range.';
    }

    public function getTypeDefinition(string $name, string $type, bool $nullable): string {
        return /** @lang GraphQL */ <<<GRAPHQL
        input {$name} {
            min: {$type}!
            max: {$type}!
        }
        GRAPHQL;
    }
}
