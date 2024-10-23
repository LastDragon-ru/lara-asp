<?php declare(strict_types = 1);

use LastDragon_ru\LaraASP\GraphQL\PackageConfig;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Direction;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Nulls;

$config = PackageConfig::getDefaultConfig();

$config->sortBy->nulls = [
    Direction::Asc->value  => Nulls::First,
    Direction::Desc->value => Nulls::Last,
];

return $config;
