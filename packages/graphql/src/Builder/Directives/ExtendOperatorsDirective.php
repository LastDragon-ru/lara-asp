<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Directives;

use GraphQL\Language\DirectiveLocation;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Override;

use function array_merge;
use function array_unique;
use function implode;

abstract class ExtendOperatorsDirective extends BaseDirective {
    public function __construct() {
        // empty
    }

    #[Override]
    public static function definition(): string {
        $name      = DirectiveLocator::directiveName(static::class);
        $locations = implode(' | ', array_unique(static::locations()));

        return <<<GRAPHQL
            """
            Extends the list of operators by the operators from the specified
            `type` or from the config if `null`.
            """
            directive @{$name}(type: String) on {$locations}
        GRAPHQL;
    }

    /**
     * @return non-empty-list<string>
     */
    protected static function locations(): array {
        return array_merge(static::getDirectiveLocations(), [
            DirectiveLocation::SCALAR,
            DirectiveLocation::ENUM,
        ]);
    }

    /**
     * @deprecated 6.0.0 Use {@see self::locations()} instead.
     *
     * @return list<string>
     */
    protected static function getDirectiveLocations(): array {
        return [
            // empty
        ];
    }

    public function getType(): ?string {
        return Cast::toStringNullable($this->directiveArgValue('type'));
    }
}
