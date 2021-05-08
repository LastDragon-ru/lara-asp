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
                class: String!
            ) on INPUT_OBJECT | INPUT_FIELD_DEFINITION
        GRAPHQL;
    }

    /**
     * @return class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\ComplexOperator>
     */
    public function getClass(): string {
        return $this->directiveArgValue('class');
    }
}
