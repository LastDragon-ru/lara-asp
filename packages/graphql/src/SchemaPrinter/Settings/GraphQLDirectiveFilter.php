<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

use GraphQL\Type\Definition\Directive;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\DirectiveFilter;
use Nuwave\Lighthouse\Support\Contracts\Directive as LighthouseDirective;

class GraphQLDirectiveFilter implements DirectiveFilter {
    public function __construct() {
        // empty
    }

    public function isAllowedDirective(Directive|LighthouseDirective $directive, bool $isStandard): bool {
        return $directive instanceof Directive
            && $directive->name === Directive::DEPRECATED_NAME;
    }
}
