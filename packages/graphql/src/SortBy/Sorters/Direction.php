<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Sorters;

use GraphQL\Type\Definition\Deprecated;
use GraphQL\Type\Definition\Description;

#[Description('Sort direction.')]
enum Direction {
    case Asc;
    case Desc;

    /**
     * @deprecated 5.4.0 Please use {@link Direction::Asc} instead.
     */
    #[Deprecated('Please use `Asc` instead.')]
    #[Description('')]
    case asc;

    /**
     * @deprecated 5.4.0 Please use {@link Direction::Desc} instead.
     */
    #[Deprecated('Please use `Desc` instead.')]
    #[Description('')]
    case desc;
}
