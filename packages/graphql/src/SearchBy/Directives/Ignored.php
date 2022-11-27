<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives;

use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Ignored as IgnoredContract;

class Ignored implements IgnoredContract {
    public static function definition(): string {
        return /** @lang GraphQL */ <<<'GRAPHQL'
            """
            Marks that field should be excluded from search.
            """
            directive @searchByIgnored on FIELD_DEFINITION | INPUT_FIELD_DEFINITION,
        GRAPHQL;
    }
}
