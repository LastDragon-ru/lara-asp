<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Enums;

enum Message {
    case Title;
    case Failed;
    case Completed;
    case Self;
    case Files;
    case Memory;
    case Read;
    case Write;
    case Delete;
    case Inout;
    case Input;
    case Output;
    case Include;
    case Exclude;
}
