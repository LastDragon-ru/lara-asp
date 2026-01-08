<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Calculator\Ast;

use Override;

class OperatorAdditionNode extends OperatorNode {
    #[Override]
    public function priority(): int {
        return 0;
    }

    #[Override]
    public function calculate(float|int $left, float|int $right): float|int {
        return $left + $right;
    }
}
