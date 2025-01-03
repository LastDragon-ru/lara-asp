<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Events;

enum FileFinishedResult {
    case Success;
    case Skipped;
    case Failed;
}
