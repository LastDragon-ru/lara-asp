<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Listeners\Console;

enum Mark: string {
    case Inout    = '↔';
    case Input    = '→';
    case Output   = '←';
    case External = '!';
    case Unknown  = '?';
}
