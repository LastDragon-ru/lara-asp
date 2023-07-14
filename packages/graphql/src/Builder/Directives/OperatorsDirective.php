<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Directives;

use GraphQL\Language\DirectiveLocation;
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
            """
            Extends the list of operators by the operators from the specified `type`.
            """
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

    public function getType(): string {
        $type = $this->directiveArgValue('type');

        assert(is_string($type));

        return $type;
    }
}
