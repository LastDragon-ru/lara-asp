<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Events;

enum DependencyResolvedResult {
    case Success;
    case Failed;
    case Null;
    case Queued;
}
