<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Directives;

use GraphQL\Language\DirectiveLocation;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scope;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\TypeUnknown;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;

use function assert;
use function implode;
use function is_string;

abstract class OperatorsDirective extends BaseDirective {
    public function __construct() {
        // empty
    }

    public static function definition(): string {
        $name      = static::getDirectiveName();
        $locations = implode(' | ', static::getDirectiveLocations());

        return /** @lang GraphQL */ <<<GRAPHQL
            directive {$name}(type: String!) on {$locations}
        GRAPHQL;
    }

    /**
     * Must start with `@` and be a valid GraphQL Directive name.
     */
    abstract protected static function getDirectiveName(): string;

    /**
     * @return non-empty-list<string>
     */
    protected static function getDirectiveLocations(): array {
        return [
            DirectiveLocation::SCALAR,
            DirectiveLocation::ENUM,
        ];
    }

    /**
     * @param class-string<Scope> $scope
     *
     * @return list<Operator>
     */
    public function getOperators(Manipulator $manipulator, string $scope): array {
        // Type
        $type = $this->directiveArgValue('type');

        assert(is_string($type));

        // Operators
        $operators = $manipulator->getTypeOperators($scope, $type);

        if (!$operators) {
            // We throw an error if operators are empty to find an invalid type
            // used with the directive.
            throw new TypeUnknown($scope, $type);
        }

        return $operators;
    }
}
