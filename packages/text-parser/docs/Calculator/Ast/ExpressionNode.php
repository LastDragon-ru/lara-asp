<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Calculator\Ast;

use function array_pop;
use function count;
use function end;
use function is_nan;

use const NAN;

/**
 * @extends ParentNode<ExpressionNodeChild>
 */
class ExpressionNode extends ParentNode implements ExpressionNodeChild {
    public function calculate(): float|int {
        /**
         * @see https://en.wikipedia.org/wiki/Reverse_Polish_notation
         * @see https://en.wikipedia.org/wiki/Shunting_yard_algorithm
         */
        $unary     = true;
        $operands  = [];
        $operators = [];

        foreach ($this->children as $child) {
            // Unary?
            if ($child instanceof OperatorNode) {
                if ($unary) {
                    $operands[] = 0;
                }

                $unary = true;
            } else {
                $unary = false;
            }

            // Process
            if ($child instanceof OperatorNode) {
                while ($operators !== [] && end($operators)->priority() >= $child->priority()) {
                    $operands[] = $this->calc(array_pop($operators), $operands);
                }

                $operators[] = $child;
            } elseif ($child instanceof self) {
                $operands[] = $child->calculate();
            } elseif ($child instanceof NumberNode) {
                $operands[] = $child->value;
            } else {
                $operands[] = NAN;
                break;
            }

            // Nan?
            if ($operands !== [] && is_nan(end($operands))) {
                $operators = [];
                break;
            }
        }

        while ($operators !== []) {
            $operands[] = $this->calc(array_pop($operators), $operands);
        }

        return count($operands) === 1 ? end($operands) : NAN;
    }

    /**
     * @param array<mixed, float|int> $operands
     */
    private function calc(OperatorNode $operator, array &$operands): float|int {
        $right = array_pop($operands) ?? NAN;
        $left  = array_pop($operands) ?? NAN;
        $value = $operator->calculate($left, $right);

        return $value;
    }
}
