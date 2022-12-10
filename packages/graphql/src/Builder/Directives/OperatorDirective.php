<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Directives;

use GraphQL\Language\AST\DirectiveNode;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;

abstract class OperatorDirective extends BaseDirective implements Operator {
    public function __construct() {
        // empty
    }

    public static function definition(): string {
        $name = static::getDirectiveName();

        return /** @lang GraphQL */ <<<GRAPHQL
            directive {$name} on INPUT_FIELD_DEFINITION
        GRAPHQL;
    }

    public function getFieldDirective(): ?DirectiveNode {
        return $this->directiveNode;
    }
}
