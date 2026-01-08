<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Calculator\Ast;

abstract class OperatorNode implements ExpressionNodeChild {
    public function __construct() {
        // empty
    }

    abstract public function priority(): int;

    abstract public function calculate(float|int $left, float|int $right): float|int;
}
