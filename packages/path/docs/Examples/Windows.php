<?php declare(strict_types = 1);

namespace LastDragon_ru\Path\Docs\Examples;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;

$base = new DirectoryPath('C:/path');

Example::dump(
    (string) $base->resolve(new FilePath('C:file.txt')),
);

Example::dump(
    (string) $base->resolve(new FilePath('D:file.txt')),
);
