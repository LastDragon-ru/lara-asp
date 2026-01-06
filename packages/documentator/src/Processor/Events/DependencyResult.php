<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Events;

enum DependencyResult {
    case Found;
    case NotFound;
    case Queued;
    case Saved;
    case Deleted;
}
