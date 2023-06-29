<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NullValueNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\Argument;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type as GraphQLType;
use GraphQL\Utils\AST;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\DefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLDefinition;

/**
 * @internal
 *
 * @extends DefinitionBlock<InputValueDefinitionNode|Argument|InputObjectField>
 */
#[GraphQLAstNode(InputValueDefinitionNode::class)]
#[GraphQLDefinition(Argument::class)]
#[GraphQLDefinition(InputObjectField::class)]
class InputValueDefinition extends DefinitionBlock {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        InputValueDefinitionNode|Argument|InputObjectField $definition,
    ) {
        parent::__construct($context, $level, $used, $definition);
    }

    protected function content(): string {
        return $this->isTypeAllowed($this->getType())
            ? parent::content()
            : '';
    }

    protected function type(int $used, bool $multiline): ?Block {
        return new Type(
            $this->getContext(),
            $this->getLevel(),
            $used,
            $this->getType(),
        );
    }

    protected function value(int $used, bool $multiline): ?Block {
        $value      = null;
        $default    = null;
        $definition = $this->getDefinition();

        if ($definition instanceof InputValueDefinitionNode) {
            $default = $definition->defaultValue;
        } else {
            $default = $definition->defaultValueExists()
                ? (AST::astFromValue($definition->defaultValue, $definition->getType()) ?? new NullValueNode([]))
                : null;
        }

        if ($default !== null) {
            $value = new Value(
                $this->getContext(),
                $this->getLevel(),
                $used,
                $default,
            );
        }

        return $value;
    }

    /**
     * @return (TypeNode&Node)|(GraphQLType&(OutputType|InputType))
     */
    private function getType(): TypeNode|GraphQLType {
        $definition = $this->getDefinition();
        $type       = $definition instanceof InputValueDefinitionNode
            ? $definition->type
            : $definition->getType();

        return $type;
    }
}
