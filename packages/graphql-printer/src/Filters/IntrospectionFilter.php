<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Filters;

use LastDragon_ru\GraphQLPrinter\Contracts\DirectiveFilter;
use LastDragon_ru\GraphQLPrinter\Contracts\TypeFilter;
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
