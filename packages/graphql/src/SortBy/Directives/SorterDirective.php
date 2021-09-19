<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Directives;

use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Sorter;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;

class SorterDirective extends BaseDirective {
    public static function definition(): string {
        return /** @lang GraphQL */ <<<'GRAPHQL'
            """
            Custom Sorter that will be used for this input field.
            """
            directive @sortBySorter(
                class: String!
            ) on INPUT_OBJECT | INPUT_FIELD_DEFINITION
        GRAPHQL;
    }

    /**
     * @return class-string<Sorter>
     */
    public function getClass(): string {
        return $this->directiveArgValue('class');
    }
}
