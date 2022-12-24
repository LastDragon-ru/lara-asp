<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Directives;

use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Ignored as IgnoredContract;

class Ignored implements IgnoredContract {
    public static function definition(): string {
        return /** @lang GraphQL */ <<<'GRAPHQL'
            """
            Marks that field should be excluded from sort.
            """
            directive @sortByIgnored on
                | FIELD_DEFINITION
                | INPUT_FIELD_DEFINITION
                | OBJECT
                | INPUT_OBJECT
                | ENUM
                | SCALAR,
        GRAPHQL;
    }
}
