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

use function mb_strlen;

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
        return $this->isTypeAllowed($this->getTypeName($this->getType()))
            ? parent::content()
            : '';
    }

    protected function type(): string|null {
        return null;
    }

    protected function body(int $used): Block|string|null {
        $type       = $this->getType();
        $default    = null;
        $definition = $this->getDefinition();

        if ($definition instanceof InputValueDefinitionNode) {
            $default = $definition->defaultValue;
        } else {
            $default = $definition->defaultValueExists()
                ? (AST::astFromValue($definition->defaultValue, $definition->getType()) ?? new NullValueNode([]))
                : null;
        }

        $space = $this->space();
        $block = $this->addUsed(
            new Type(
                $this->getContext(),
                $this->getLevel(),
                $this->getUsed(),
                $type,
            ),
        );
        $body  = ":{$space}{$block}";

        if ($default !== null) {
            $prefix = "{$body}{$space}={$space}";
            $value  = $this->addUsed(
                new Value(
                    $this->getContext(),
                    $this->getLevel(),
                    $this->getUsed() + mb_strlen($prefix),
                    $default,
                ),
            );
            $body   = "{$prefix}{$value}";
        }

        return $body;
    }

    protected function fields(int $used): Block|string|null {
        return null;
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
