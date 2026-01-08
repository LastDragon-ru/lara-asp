<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Calculator\Ast;

use LastDragon_ru\TextParser\Ast\NodeParentFactory;
use LogicException;
use Override;

use function end;

/**
 * @extends NodeParentFactory<ExpressionNode, ExpressionNodeChild>
 */
class ExpressionNodeFactory extends NodeParentFactory {
    /**
     * @inheritDoc
     */
    #[Override]
    protected function onCreate(array $children): ?object {
        // Expression cannot be empty
        return $children !== [] ? new ExpressionNode($children) : null;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function onPush(array $children, ?object $node): bool {
        // Operator is always allowed
        if ($node instanceof OperatorNode) {
            return true;
        }

        // Other nodes must be separated by any Operator
        $previous = end($children);
        $valid    = $previous === false || $previous instanceof OperatorNode;

        if (!$valid) {
            throw new LogicException('Operator is missing.');
        }

        return true;
    }
}
