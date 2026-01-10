<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\List;

/**
 * @internal
 */
enum Position {
    case Before;
    case After;
    case TouchStart;
    case TouchEnd;
    case Wrap;
    case Same;
    case Inside;
    case Intersect;
}
