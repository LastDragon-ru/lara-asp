<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Traits;

use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;

use function implode;

/**
 * @mixin Operator
 */
trait DirectiveName {
    public static function getDirectiveName(): string {
        return implode('', [
            '@',
            Str::camel(Directive::Name),
            'Operator',
            Str::studly(static::getName()),
        ]);
    }
}
