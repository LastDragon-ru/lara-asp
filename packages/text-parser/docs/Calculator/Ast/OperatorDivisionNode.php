<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Calculator\Ast;

use DivisionByZeroError;
use Override;

use const NAN;

class OperatorDivisionNode extends OperatorNode {
    #[Override]
    public function priority(): int {
        return 1;
    }

    #[Override]
    public function calculate(float|int $left, float|int $right): float|int {
        try {
            return $left / $right;
        } catch (DivisionByZeroError) {
            return NAN;
        }
    }
}
