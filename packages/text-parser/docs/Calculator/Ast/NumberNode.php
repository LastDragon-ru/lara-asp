<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Calculator\Ast;

class NumberNode implements ExpressionNodeChild {
    public function __construct(
        public int $value,
    ) {
        // empty
    }
}
