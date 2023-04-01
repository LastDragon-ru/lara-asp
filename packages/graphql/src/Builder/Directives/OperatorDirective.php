<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Directives;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\DirectiveLocation;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scope;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;

use function implode;
use function is_a;

abstract class OperatorDirective extends BaseDirective implements Operator {
    public function __construct() {
        // empty
    }

    public static function definition(): string {
        $name      = static::getDirectiveName();
        $locations = implode('|', static::getDirectiveLocations());

        return /** @lang GraphQL */ <<<GRAPHQL
            directive {$name} on {$locations}
        GRAPHQL;
    }

    /**
     * @return non-empty-list<string>
     */
    protected static function getDirectiveLocations(): array {
        $locations = [
            DirectiveLocation::INPUT_FIELD_DEFINITION,
        ];

        if (is_a(static::class, Scope::class, true)) {
            $locations[] = DirectiveLocation::SCALAR;
            $locations[] = DirectiveLocation::ENUM;
        }

        return $locations;
    }

    public function getFieldDirective(): ?DirectiveNode {
        return $this->directiveNode ?? null;
    }
}
