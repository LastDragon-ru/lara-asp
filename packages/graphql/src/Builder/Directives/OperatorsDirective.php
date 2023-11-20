<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Directives;

use GraphQL\Language\DirectiveLocation;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Override;

use function assert;
use function implode;
use function is_string;

abstract class OperatorsDirective extends BaseDirective {
    public function __construct() {
        // empty
    }

    #[Override]
    public static function definition(): string {
        $name      = DirectiveLocator::directiveName(static::class);
        $locations = implode(' | ', static::getDirectiveLocations());

        return <<<GRAPHQL
            """
            Extends the list of operators by the operators from the specified `type`.
            """
            directive @{$name}(type: String!) on {$locations}
        GRAPHQL;
    }

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
