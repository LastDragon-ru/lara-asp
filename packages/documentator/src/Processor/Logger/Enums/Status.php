<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums;

enum Status {
    case Done;
    case Null;
    case Skip;
    case Next;
    case Use;
    case Save;
    case Fail;
}
