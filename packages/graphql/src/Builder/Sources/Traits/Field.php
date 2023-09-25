<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Sources\Traits;

use Closure;
use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Type\Definition\Argument;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InputFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\InterfaceFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\ObjectFieldSource;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;

use function count;

/**
 * @mixin InputFieldSource|InterfaceFieldSource|ObjectFieldSource
 * @internal
 */
trait Field {
    public function getName(): string {
        return $this->getManipulator()->getName($this->getField());
    }

    public function hasArguments(): bool {
        $node = $this->getField();
        $args = false;

        if ($node instanceof FieldDefinitionNode) {
            $args = $node->arguments->count() > 0;
        } else {
            $args = count($node->args) > 0;
        }

        return $args;
    }

    /**
     * @param Closure(InputValueDefinitionNode|Argument|ArgumentNode, AstManipulator): bool $closure
     */
    public function hasArgument(Closure $closure): bool {
        $manipulator = $this->getManipulator();
        $node        = $this->getField();
        $arg         = $manipulator->findArgument(
            $node,
            static function (mixed $argument) use ($manipulator, $closure): bool {
                return $closure($argument, $manipulator);
            },
        );

        return $arg !== null;
    }

    public function __toString(): string {
        $field = $this->getName();
        $type  = $this->getManipulator()->getTypeFullName($this->getObject());

        return "{$type} { {$field} }";
    }
}
