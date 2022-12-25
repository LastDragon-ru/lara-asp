<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator as BuilderOperator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Operators as BuilderOperators;
use LastDragon_ru\LaraASP\GraphQL\Package;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Directives\Directive;

use function config;

class Operators extends BuilderOperators {
    public const Extra = 'Extra';

    public function __construct() {
        /** @var array<string,array<class-string<BuilderOperator>|string>> $operators */
        $operators = (array) config(Package::Name.'.sort_by.operators');


        parent::__construct($operators);
    }

    public function getScope(): string {
        return Directive::getScope();
    }
}
