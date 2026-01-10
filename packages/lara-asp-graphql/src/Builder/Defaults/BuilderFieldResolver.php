<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Defaults;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderFieldResolver as BuilderFieldResolverContract;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use Override;

use function implode;

/**
 * @internal
 */
final readonly class BuilderFieldResolver implements BuilderFieldResolverContract {
    public function __construct() {
        // empty
    }

    #[Override]
    public function getField(object $builder, Field $field): string {
        return implode('.', $field->getPath());
    }
}
