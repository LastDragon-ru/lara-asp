<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\FragmentSpreadNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\DefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\ExecutableDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;

/**
 * @internal
 *
 * @extends DefinitionBlock<FragmentSpreadNode>
 */
#[GraphQLAstNode(FragmentSpreadNode::class)]
class FragmentSpread extends DefinitionBlock implements ExecutableDefinitionBlock {
    /**
     * @param (TypeNode&Node)|Type|null $type
     */
    public function __construct(
        Context $context,
        FragmentSpreadNode $definition,
        private TypeNode|Type|null $type = null,
    ) {
        parent::__construct($context, $definition);
    }

    protected function prefix(): ?string {
        return '...';
    }

    public function name(): string {
        return $this->getDefinition()->name->value;
    }

    protected function content(Collector $collector, int $level, int $used): string {
        // Print?
        if (!$this->isTypeAllowed($this->type)) {
            return '';
        }

        // Convert
        $content = parent::content($collector, $level, $used);

        // Statistics
        if ($content && $this->type) {
            $collector->addUsedType($this->getTypeName($this->type));
        }

        // Return
        return $content;
    }
}
