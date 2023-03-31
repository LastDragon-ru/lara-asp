<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives;

use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\OperatorsDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Scope;

use function implode;

class Operators extends OperatorsDirective implements Scope {
    protected static function getDirectiveName(): string {
        return implode('', ['@', Str::camel(Directive::Name), 'Operators']);
    }
}
