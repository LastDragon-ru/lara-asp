<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use GraphQL\Type\Definition\Deprecated;
use GraphQL\Type\Definition\Description;

#[Description('Sort direction.')]
enum Direction: string {
    case Asc  = 'Asc';
    case Desc = 'Desc';

    /**
     * @internal
     * @deprecated 5.4.0 Please use {@link Direction::Asc} instead.
     */
    #[Deprecated('Please use `Asc` instead.')]
    #[Description('')]
    case asc = 'asc';

    /**
     * @internal
     * @deprecated 5.4.0 Please use {@link Direction::Desc} instead.
     */
    #[Deprecated('Please use `Desc` instead.')]
    #[Description('')]
    case desc = 'desc';
}
