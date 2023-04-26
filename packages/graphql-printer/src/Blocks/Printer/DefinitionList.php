<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Printer;

use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\NamedType;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

use function rtrim;

/**
 * @internal
 * @extends ListBlock<Block>
 */
class DefinitionList extends ListBlock {
    public function __construct(
        Context $context,
        int $level,
        protected bool $root = false,
    ) {
        parent::__construct($context, $level);
    }

    protected function isWrapped(): bool {
        return true;
    }

    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeSchema();
    }

    protected function isAlwaysMultiline(): bool {
        return true;
    }

    protected function isRoot(): bool {
        return $this->root;
    }

    protected function content(): string {
        $content = parent::content();

        if ($content && $this->isRoot()) {
            $eof     = $this->getSettings()->getFileEnd();
            $content = rtrim($content);
            $content = "{$this->indent()}{$content}{$eof}";
        }

        return $content;
    }

    protected function analyze(Block $block): Block {
        $block = $this->addUsed($block);

        if ($block instanceof DefinitionBlock) {
            $definition = $block->getDefinition();

            if ($definition instanceof NamedType) {
                $this->addUsedType($definition->name());
            } elseif ($definition instanceof Directive) {
                $this->addUsedDirective("@{$definition->name}");
            } else {
                // empty
            }
        }

        return $block;
    }
}
