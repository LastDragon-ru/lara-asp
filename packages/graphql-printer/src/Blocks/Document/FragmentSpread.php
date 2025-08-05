<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\FragmentSpreadNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\GraphQLPrinter\Blocks\Types\DefinitionBlock;
use LastDragon_ru\GraphQLPrinter\Blocks\Types\ExecutableDefinitionBlock;
use LastDragon_ru\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use LastDragon_ru\GraphQLPrinter\Testing\Package\GraphQLAstNode;
use Override;

/**
 * @internal
 *
 * @extends DefinitionBlock<FragmentSpreadNode>
 */
#[GraphQLAstNode(FragmentSpreadNode::class)]
class FragmentSpread extends DefinitionBlock implements ExecutableDefinitionBlock {
    public function __construct(
        Context $context,
        FragmentSpreadNode $definition,
        private (TypeNode&Node)|Type|null $type,
    ) {
        parent::__construct($context, $definition);
    }

    #[Override]
    protected function prefix(): ?string {
        return '...';
    }

    #[Override]
    protected function name(): string {
        return $this->getDefinition()->name->value;
    }

    #[Override]
    protected function content(Collector $collector, int $level, int $used): string {
        // Print?
        if (!$this->isTypeAllowed($this->type)) {
            return '';
        }

        // Convert
        $content = parent::content($collector, $level, $used);

        // Statistics
        if ($content !== '' && $this->type !== null) {
            $collector->addUsedType($this->getTypeName($this->type));
        }

        // Return
        return $content;
    }
}
