<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Filters;

use GraphQL\Type\Definition\Directive;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\DirectiveFilter;
use Override;

class GraphQLDirectiveFilter implements DirectiveFilter {
    public function __construct() {
        // empty
    }

    #[Override]
    public function isAllowedDirective(string $directive, bool $isStandard): bool {
        return $directive === Directive::DEPRECATED_NAME;
    }
}
