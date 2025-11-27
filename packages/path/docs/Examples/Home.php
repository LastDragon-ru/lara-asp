<?php declare(strict_types = 1);

namespace LastDragon_ru\Path\Docs\Examples;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;

$home = new DirectoryPath('~/path');
$file = new FilePath('file.txt');

Example::dump($home->type);
Example::dump((string) $home->resolve($file));
Example::dump((string) $home->file('../../../file.md')); // !
