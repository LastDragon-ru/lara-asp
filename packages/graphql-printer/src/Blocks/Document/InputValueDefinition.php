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
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLDefinition;
use Override;

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
        InputValueDefinitionNode|Argument|InputObjectField $definition,
    ) {
        parent::__construct($context, $definition);
    }

    #[Override]
    protected function content(Collector $collector, int $level, int $used): string {
        return $this->isTypeAllowed($this->getType())
            ? parent::content($collector, $level, $used)
            : '';
    }

    #[Override]
    protected function type(bool $multiline): ?Block {
        return new Type(
            $this->getContext(),
            $this->getType(),
        );
    }

    #[Override]
    protected function value(bool $multiline): ?Block {
        $type       = null;
        $value      = null;
        $default    = null;
        $definition = $this->getDefinition();

        if ($definition instanceof InputValueDefinitionNode) {
            $type    = $definition->type;
            $default = $definition->defaultValue;
        } else {
            $type    = $definition->getType();
            $default = $definition->defaultValueExists()
                ? (AST::astFromValue($definition->defaultValue, $definition->getType()) ?? new NullValueNode([]))
                : null;
        }

        if ($default !== null) {
            $value = new Value(
                $this->getContext(),
                $default,
                $type,
            );
        }

        return $value;
    }

    private function getType(): (TypeNode&Node)|(GraphQLType&OutputType)|(GraphQLType&InputType) {
        $definition = $this->getDefinition();
        $type       = $definition instanceof InputValueDefinitionNode
            ? $definition->type
            : $definition->getType();

        return $type;
    }
}
