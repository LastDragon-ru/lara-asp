<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

enum Mark: string {
    case Hook     = '@';
    case Inout    = '↔';
    case Input    = '→';
    case Output   = '←';
    case External = '!';
}
