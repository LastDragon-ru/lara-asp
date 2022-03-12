<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\SchemaPrinter;

use GraphQL\Type\Definition\Directive as GraphQLDirective;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\DirectiveFilter;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\Directive as LighthouseDirective;

use function explode;
use function str_starts_with;

class LighthouseDirectiveFilter implements DirectiveFilter {
    public function isAllowedDirective(GraphQLDirective|LighthouseDirective $directive, bool $isStandard): bool {
        return $isStandard
            || $directive instanceof GraphQLDirective
            || !str_starts_with($directive::class, explode('\\', BaseDirective::class)[0]);
    }
}
