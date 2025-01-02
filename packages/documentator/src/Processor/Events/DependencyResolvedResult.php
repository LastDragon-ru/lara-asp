<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Events;

enum DependencyResolvedResult {
    case Success;
    case Missed;
    case Failed;
    case Null;
}
