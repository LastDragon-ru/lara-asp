<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\PropertyDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator as Marker;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;

use function implode;

class Property extends PropertyDirective implements Marker {
    public static function getDirectiveName(): string {
        return implode('', [
            '@',
            Str::camel(Directive::Name),
            Str::studly(static::getName()),
        ]);
    }

    public function getFieldDescription(): string {
        return 'Property condition.';
    }
}
