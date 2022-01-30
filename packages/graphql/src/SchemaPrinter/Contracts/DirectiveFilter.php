<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts;

use GraphQL\Type\Definition\Directive as GraphQLDirective;
use Nuwave\Lighthouse\Support\Contracts\Directive as LighthouseDirective;

interface DirectiveFilter {
    public function isAllowedDirective(GraphQLDirective|LighthouseDirective $directive): bool;
}
