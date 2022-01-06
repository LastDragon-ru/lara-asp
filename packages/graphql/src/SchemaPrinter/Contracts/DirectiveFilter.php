<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts;

use GraphQL\Language\AST\DirectiveNode;

interface DirectiveFilter {
    public function isAllowedDirective(DirectiveNode $directive): bool;
}
