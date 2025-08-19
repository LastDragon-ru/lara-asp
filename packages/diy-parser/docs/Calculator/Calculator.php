<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Docs\Calculator;

use const NAN;

class Calculator {
    public function __construct() {
        // empty
    }

    public function calculate(string $expression): float|int {
        return (new Parser())->parse($expression)?->calculate() ?? NAN;
    }
}
