<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives;

use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Complex\Relation;

class RelationOperatorDirective extends OperatorDirective {
    public static function definition(): string {
        return /** @lang GraphQL */ <<<'GRAPHQL'
            """
            Relation operator.
            """
            directive @searchByRelation on INPUT_OBJECT | INPUT_FIELD_DEFINITION
        GRAPHQL;
    }

    public function getClass(): string {
        return Relation::class;
    }
}
