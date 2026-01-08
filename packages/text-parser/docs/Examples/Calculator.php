<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Examples;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\TextParser\Docs\Calculator\Calculator;

Example::dump(
    (new Calculator())->calculate('2 - (1 + 2) / 3'),
);
