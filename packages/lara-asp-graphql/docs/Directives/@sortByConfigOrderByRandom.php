<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\GraphQL\PackageConfig;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions\SortByOperatorRandomDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators;

$config = PackageConfig::getDefaultConfig();

$config->sortBy->operators[Operators::Extra] = [
    SortByOperatorRandomDirective::class,
];

return $config;
