<?php declare(strict_types = 1);

namespace LastDragon_ru\Path\Docs\Examples;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\Path\FilePath;

Example::dump(
    (new FilePath('//server/share/path/to/file.txt'))->type,
);
