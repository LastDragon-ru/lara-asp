<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Directives;

use GraphQL\Language\DirectiveLocation;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Override;

use function array_merge;
use function array_unique;
use function implode;

abstract class IgnoredDirective extends BaseDirective {
    public function __construct() {
        // empty
    }

    #[Override]
    public static function definition(): string {
        $name      = DirectiveLocator::directiveName(static::class);
        $locations = implode(' | ', array_unique(static::locations()));

        return <<<GRAPHQL
            """
            Marks that field/definition should be excluded.
            """
            directive @{$name} on {$locations}
        GRAPHQL;
    }

    /**
     * @return non-empty-list<string>
     */
    protected static function locations(): array {
        return array_merge(static::getDirectiveLocations(), [
            DirectiveLocation::FIELD_DEFINITION,
            DirectiveLocation::INPUT_FIELD_DEFINITION,
            DirectiveLocation::OBJECT,
            DirectiveLocation::INPUT_OBJECT,
            DirectiveLocation::ENUM,
            DirectiveLocation::SCALAR,
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
}
