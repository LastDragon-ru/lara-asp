<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package;

use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\Directive;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;
use Override;

/**
 * @internal
 */
class TestDirective implements Directive, FieldResolver {
    #[Override]
    public static function definition(): string {
        $name = DirectiveLocator::directiveName(static::class);

        return <<<GRAPHQL
            directive @{$name} on FIELD_DEFINITION
        GRAPHQL;
    }

    #[Override]
    public function resolveField(FieldValue $fieldValue): callable {
        return static fn () => null;
    }
}
