<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives;

use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Ignored as IgnoredContract;
use Nuwave\Lighthouse\Support\Contracts\Directive;

class Ignored implements Directive, IgnoredContract {
    public static function definition(): string {
        return <<<'GRAPHQL'
            """
            Marks that field should be excluded from search.
            """
            directive @searchByIgnored on
                | FIELD_DEFINITION
                | INPUT_FIELD_DEFINITION
                | OBJECT
                | INPUT_OBJECT
                | ENUM
                | SCALAR
        GRAPHQL;
    }
}
