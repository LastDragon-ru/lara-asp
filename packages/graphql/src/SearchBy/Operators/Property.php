<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\PropertyDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;

use function implode;

class Property extends PropertyDirective implements Operator {
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

    public function getFieldType(TypeProvider $provider, string $type): ?string {
        return null;
    }
}
