<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Directives;

use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Ignored as IgnoredContract;
use Nuwave\Lighthouse\Support\Contracts\Directive;

class Ignored implements Directive, IgnoredContract {
    public static function definition(): string {
        return <<<'GraphQL'
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
        GraphQL;
    }
}
