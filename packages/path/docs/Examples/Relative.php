<?php declare(strict_types = 1);

namespace LastDragon_ru\Path\Docs\Examples;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;

$base = new DirectoryPath('~/path/to/directory');

Example::dump((string) $base->relative(new FilePath('~/file.txt')));
Example::dump($base->relative(new FilePath('/file.txt'))); // `null`, because type differ
