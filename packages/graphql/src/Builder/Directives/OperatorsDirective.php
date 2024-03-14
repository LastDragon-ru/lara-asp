<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Directives;

use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Override;

use function array_unique;
use function assert;
use function implode;
use function is_string;

/**
 * @deprecated 5.6.0 Use {@see ExtendOperatorsDirective} instead.
 */
abstract class OperatorsDirective extends ExtendOperatorsDirective {
    #[Override]
    public static function definition(): string {
        $name      = DirectiveLocator::directiveName(static::class);
        $locations = implode(' | ', array_unique(static::locations()));

        return <<<GRAPHQL
            """
            Extends the list of operators by the operators from the specified `type`.

            The directive is deprecated!
            """
            directive @{$name}(type: String!) on {$locations}
        GRAPHQL;
    }

    #[Override]
    public function getType(): string {
        $type = $this->directiveArgValue('type');

        assert(is_string($type));

        return $type;
    }
}
