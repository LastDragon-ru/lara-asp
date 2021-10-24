<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Directives;

use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Unsortable as UnsortableContract;

class Unsortable implements UnsortableContract {
    public static function definition(): string {
        return /** @lang GraphQL */ <<<'GRAPHQL'
            """
            Marks that field should be excluded from sort.
            """
            directive @sortByUnsortable on FIELD_DEFINITION | INPUT_FIELD_DEFINITION,
        GRAPHQL;
    }
}
