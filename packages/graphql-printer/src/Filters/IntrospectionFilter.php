<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Filters;

use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\DirectiveFilter;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\TypeFilter;
use Override;

/**
 * @internal
 */
class IntrospectionFilter implements TypeFilter, DirectiveFilter {
    #[Override]
    public function isAllowedDirective(string $directive, bool $isStandard): bool {
        return true;
    }

    #[Override]
    public function isAllowedType(string $type, bool $isStandard): bool {
        return true;
    }
}
