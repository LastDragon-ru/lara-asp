<?php declare(strict_types = 1);

namespace LastDragon_ru\Path\Docs\Examples;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;

$baseDirectory = new DirectoryPath('/path/to/directory');
$baseFile      = new FilePath('/path/to/directory/file.md');
$file          = new FilePath('../file.txt');

Example::dump((string) $baseDirectory->resolve($file));
Example::dump((string) $baseFile->resolve($file));
Example::dump((string) $baseFile->file('../../file.md'));
