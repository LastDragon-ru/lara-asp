<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\Directives;

use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Support\Contracts\Directive;
use Override;

/**
 * @internal
 */
class TestDirective implements Directive {
    #[Override]
    public static function definition(): string {
        $name = DirectiveLocator::directiveName(static::class);

        return <<<GRAPHQL
            directive {$name} on FIELD_DEFINITION
        GRAPHQL;
    }
}
