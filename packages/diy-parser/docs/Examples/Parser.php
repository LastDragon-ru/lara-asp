<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Docs\Examples;

use LastDragon_ru\DiyParser\Docs\Calculator\Parser;
use LastDragon_ru\LaraASP\Dev\App\Example;

Example::dump(
    (new Parser())->parse('2 - (1 + 2) / 3'),
);
