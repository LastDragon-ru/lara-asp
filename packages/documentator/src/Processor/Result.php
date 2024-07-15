<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

enum Result {
    case Success;
    case Failed;
    case Skipped;
    case Missed;
}
