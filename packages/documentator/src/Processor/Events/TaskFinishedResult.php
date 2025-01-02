<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Events;

enum TaskFinishedResult {
    case Success;
    case Failed;
}
