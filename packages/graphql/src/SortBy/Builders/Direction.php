<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Builders;

use GraphQL\Type\Definition\Description;

#[Description('Sort direction.')]
enum Direction {
    case asc;
    case desc;
}
