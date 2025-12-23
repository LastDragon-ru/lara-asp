<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console\Enums;

enum Mark {
    case Dot;
    case Done;
    case Fail;
    case Info;
    case Task;
    case Hook;
    case Fill;
    case Inout;
    case Input;
    case Output;
    case External;
}
