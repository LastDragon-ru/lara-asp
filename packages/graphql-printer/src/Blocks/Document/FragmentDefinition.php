<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\FragmentDefinitionNode;
use LastDragon_ru\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\GraphQLPrinter\Blocks\Types\DefinitionBlock;
use LastDragon_ru\GraphQLPrinter\Blocks\Types\ExecutableDefinitionBlock;
use LastDragon_ru\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use LastDragon_ru\GraphQLPrinter\Package\GraphQLAstNode;
use Override;

/**
 * @internal
 *
 * @extends DefinitionBlock<FragmentDefinitionNode>
 */
#[GraphQLAstNode(FragmentDefinitionNode::class)]
class FragmentDefinition extends DefinitionBlock implements ExecutableDefinitionBlock {
    #[Override]
    protected function prefix(): ?string {
        return 'fragment';
    }

    #[Override]
    protected function content(Collector $collector, int $level, int $used): string {
        // Print?
        if (!$this->isTypeAllowed($this->getDefinition()->typeCondition)) {
            return '';
        }

        // Convert
        $content = parent::content($collector, $level, $used);

        // Statistics
        if ($content !== '') {
            $collector->addUsedType($this->getTypeName($this->getDefinition()->typeCondition));
        }

        // Return
        return $content;
    }

    #[Override]
    protected function body(bool $multiline): ?Block {
        return new class($this->getContext(), $this->getDefinition()) extends Block {
            public function __construct(
                Context $context,
                private FragmentDefinitionNode $definition,
            ) {
                parent::__construct($context);
            }

            #[Override]
            protected function content(Collector $collector, int $level, int $used): string {
                $type    = $this->definition->typeCondition->name->value;
                $content = "on {$type}";

                $collector->addUsedType($type);

                return $content;
            }
        };
    }

    #[Override]
    protected function fields(bool $multiline): ?Block {
        $definition = $this->getDefinition();
        $fields     = new SelectionSet(
            $this->getContext(),
            $definition->selectionSet,
            $definition->typeCondition,
        );

        return $fields;
    }
}
