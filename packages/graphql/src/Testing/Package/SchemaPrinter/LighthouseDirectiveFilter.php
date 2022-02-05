<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\SchemaPrinter;

use GraphQL\Type\Definition\Directive as GraphQLDirective;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\DirectiveFilter;
use Nuwave\Lighthouse\Schema\Directives\AllDirective;
use Nuwave\Lighthouse\Schema\Directives\FieldDirective;
use Nuwave\Lighthouse\Support\Contracts\Directive as LighthouseDirective;

class LighthouseDirectiveFilter implements DirectiveFilter {
    public function isAllowedDirective(GraphQLDirective|LighthouseDirective $directive): bool {
        return !($directive instanceof AllDirective)
            && !($directive instanceof FieldDirective);
    }
}
