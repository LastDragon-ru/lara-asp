<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\LaraASP\Documentator\Package;

enum Hook: string {
    case Before = Package::Name.':before';
    case After  = Package::Name.':after';
    case Each   = Package::Name.':each';
}
