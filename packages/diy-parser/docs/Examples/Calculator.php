<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Docs\Examples;

use LastDragon_ru\DiyParser\Docs\Calculator\Calculator;
use LastDragon_ru\LaraASP\Dev\App\Example;

Example::dump(
    (new Calculator())->calculate('2 - (1 + 2) / 3'),
);
