<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\GraphQL\PackageConfig;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorBetweenDirective;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Definitions\SearchByOperatorEqualDirective;

$config = PackageConfig::getDefaultConfig();

// You can define a list of operators for each type
$config->searchBy->operators['Date'] = [
    SearchByOperatorEqualDirective::class,
    SearchByOperatorBetweenDirective::class,
    // MyCustomOperator::class,
];

// Or re-use existing type
$config->searchBy->operators['DateTime'] = [
    'Date',
];

// Or re-use built-in type
$config->searchBy->operators['Int'] = [
    'Int',                      // built-in operators for `Int` will be used
    // MyCustomOperator::class, // the custom operator will be added
];

// You can also use enum name to redefine default operators for it:
$config->searchBy->operators['MyEnum'] = [
    'Boolean',
];

// Return
return $config;
