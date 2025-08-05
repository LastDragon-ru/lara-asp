<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\GraphQLPrinter\Blocks\Types\DefinitionBlock;
use LastDragon_ru\GraphQLPrinter\Blocks\Types\ExecutableDefinitionBlock;
use LastDragon_ru\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use LastDragon_ru\GraphQLPrinter\Testing\Package\GraphQLAstNode;
use Override;

/**
 * @internal
 *
 * @extends DefinitionBlock<FieldNode>
 */
#[GraphQLAstNode(FieldNode::class)]
class Field extends DefinitionBlock implements ExecutableDefinitionBlock {
    public function __construct(
        Context $context,
        FieldNode $definition,
        private (TypeNode&Node)|Type|null $type,
    ) {
        parent::__construct($context, $definition);
    }

    #[Override]
    protected function name(): string {
        return $this->getDefinition()->name->value;
    }

    #[Override]
    protected function prefix(): ?string {
        $definition = $this->getDefinition();
        $type       = $definition->alias !== null
            ? "{$definition->alias->value}:"
            : '';

        return $type;
    }

    #[Override]
    protected function content(Collector $collector, int $level, int $used): string {
        // Print?
        $parent = $this->type;
        $type   = $parent !== null
            ? $this->getContext()->getField($parent, $this->name())?->getType()
            : null;

        if (!$this->isTypeAllowed($parent) || !$this->isTypeAllowed($type)) {
            return '';
        }

        // Convert
        $content = parent::content($collector, $level, $used);

        // Statistics
        if ($content !== '') {
            if ($parent !== null) {
                $collector->addUsedType($this->getTypeName($parent));
            }

            if ($type !== null) {
                $collector->addUsedType($this->getTypeName($type));
            }
        }

        // Return
        return $content;
    }

    #[Override]
    protected function arguments(bool $multiline): ?Block {
        $definition = $this->getDefinition();
        $context    = $this->getContext();
        $field      = $this->name();
        $type       = $this->type;
        $args       = new Arguments(
            $context,
            $definition->arguments,
            static function (ArgumentNode $argument) use ($context, $type, $field): TypeNode|Type|null {
                $name = $argument->name->value;
                $type = $type !== null
                    ? $context->getFieldArgument($type, $field, $name)?->getType()
                    : null;

                return $type;
            },
        );

        return $args;
    }

    #[Override]
    protected function fields(bool $multiline): ?Block {
        $definition = $this->getDefinition();
        $type       = $this->type !== null
            ? $this->getContext()->getField($this->type, $this->name())?->getType()
            : null;
        $set        = $definition->selectionSet !== null
            ? new SelectionSet($this->getContext(), $definition->selectionSet, $type)
            : null;

        return $set;
    }
}
