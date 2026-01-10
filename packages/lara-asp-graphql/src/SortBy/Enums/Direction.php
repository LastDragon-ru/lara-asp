<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Enums;

use GraphQL\Type\Definition\Description;

#[Description('Sort direction.')]
enum Direction: string {
    case Asc  = 'Asc';
    case Desc = 'Desc';
}
