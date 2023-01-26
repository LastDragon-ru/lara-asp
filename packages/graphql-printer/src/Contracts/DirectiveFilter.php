<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Contracts;

interface DirectiveFilter {
    public function isAllowedDirective(string $directive, bool $isStandard): bool;
}
