<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives;

use Nuwave\Lighthouse\Schema\Directives\BaseDirective;

class OperatorDirective extends BaseDirective {
    public static function definition(): string {
        return /** @lang GraphQL */ <<<'GRAPHQL'
            """
            Complex operator that will be used for this input field.
            """
            directive @searchByOperator(
                name: String!
            ) on INPUT_FIELD_DEFINITION
        GRAPHQL;
    }

    public function getName(): string {
        return $this->directiveArgValue('name');
    }
}
