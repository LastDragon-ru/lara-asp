<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Directives;

use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Ignored as IgnoredContract;
use Nuwave\Lighthouse\Support\Contracts\Directive;
use Override;

class Ignored implements Directive, IgnoredContract {
    #[Override]
    public static function definition(): string {
        return <<<'GRAPHQL'
            """
            Marks that field/definition should be excluded from sort.
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
