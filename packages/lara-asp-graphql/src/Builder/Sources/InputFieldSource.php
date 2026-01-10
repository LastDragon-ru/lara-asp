<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Sources;

use Closure;
use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\Argument;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Builder\Sources\Traits\Field;
use LastDragon_ru\LaraASP\GraphQL\Utils\AstManipulator;

/**
 * @extends Source<(TypeNode&Node)|Type, InputSource>
 */
class InputFieldSource extends Source {
    use Field;

    public function __construct(
        AstManipulator $manipulator,
        InputSource $parent,
        private InputValueDefinitionNode|InputObjectField $field,
        (TypeNode&Node)|Type|null $type = null,
    ) {
        parent::__construct(
            $manipulator,
            $type ?? ($field instanceof InputObjectField ? $field->getType() : $field->type),
            $parent,
        );
    }

    // <editor-fold desc="Getters / Setters">
    // =========================================================================
    public function getObject(): InputObjectTypeDefinitionNode|InputObjectType {
        return $this->getParent()->getType();
    }

    public function getField(): InputValueDefinitionNode|InputObjectField {
        return $this->field;
    }

    public function hasArguments(): bool {
        return false;
    }

    /**
     * @param Closure(InputValueDefinitionNode|Argument|ArgumentNode, AstManipulator): bool $closure
     */
    public function hasArgument(Closure $closure): bool {
        return false;
    }
    // </editor-fold>
}
