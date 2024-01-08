<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Enums;

use GraphQL\Type\Definition\Deprecated;
use GraphQL\Type\Definition\Description;

enum Flag {
    case Yes;

    /**
     * @deprecated 5.4.0 Please use {@link Flag::Yes} instead.
     */
    #[Deprecated('Please use `Yes` instead.')]
    #[Description('')]
    case yes;
}
