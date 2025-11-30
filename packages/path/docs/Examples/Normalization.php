<?php declare(strict_types = 1);

namespace LastDragon_ru\Path\Docs\Examples;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;

$base = new DirectoryPath('\\path\\.\\to\\directory');
$file = new FilePath('../path/../to/../file.txt');
$win  = new FilePath('c:/path/../to/../file.txt');

Example::dump((string) $base->resolve($file));
Example::dump((string) $win->normalized());
