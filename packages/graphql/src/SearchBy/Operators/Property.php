<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\PropertyDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\Operator as Marker;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Traits\DirectiveName;

class Property extends PropertyDirective implements Marker {
    use DirectiveName;

    public function getFieldDescription(): string {
        return 'Property condition.';
    }
}
