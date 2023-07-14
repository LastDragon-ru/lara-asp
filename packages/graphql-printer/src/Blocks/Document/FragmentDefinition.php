<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\FragmentDefinitionNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\DefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\ExecutableDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;

/**
 * @internal
 *
 * @extends DefinitionBlock<FragmentDefinitionNode>
 */
#[GraphQLAstNode(FragmentDefinitionNode::class)]
class FragmentDefinition extends DefinitionBlock implements ExecutableDefinitionBlock {
    protected function prefix(): ?string {
        return 'fragment';
    }

    protected function content(Collector $collector, int $level, int $used): string {
        // Print?
        if (!$this->isTypeAllowed($this->getDefinition()->typeCondition)) {
            return '';
        }

        // Convert
        $content = parent::content($collector, $level, $used);

        // Statistics
        if ($content) {
            $collector->addUsedType($this->getTypeName($this->getDefinition()->typeCondition));
        }

        // Return
        return $content;
    }

    protected function body(bool $multiline): ?Block {
        return new class($this->getContext(), $this->getDefinition()) extends Block {
            public function __construct(
                Context $context,
                private FragmentDefinitionNode $definition,
            ) {
                parent::__construct($context);
            }

            protected function content(Collector $collector, int $level, int $used): string {
                $type    = $this->definition->typeCondition->name->value;
                $content = "on {$type}";

                $collector->addUsedType($type);

                return $content;
            }
        };
    }

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
