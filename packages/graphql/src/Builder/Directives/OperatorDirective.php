<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Directives;

use GraphQL\Language\DirectiveLocation;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderPropertyResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scope;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Override;

use function implode;
use function is_a;

abstract class OperatorDirective extends BaseDirective implements Operator {
    public function __construct(
        protected readonly BuilderPropertyResolver $resolver,
    ) {
        // empty
    }

    #[Override]
    public static function definition(): string {
        $name      = '@'.DirectiveLocator::directiveName(static::class);
        $locations = implode('|', static::getDirectiveLocations());

        return <<<GRAPHQL
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
}
