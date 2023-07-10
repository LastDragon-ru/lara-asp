<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use Closure;
use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\ListBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

/**
 * @internal
 * @extends ListBlock<Argument, array-key, ArgumentNode>
 */
class Arguments extends ListBlock {
    /**
     * @param iterable<array-key, ArgumentNode>                  $items
     * @param Closure(ArgumentNode): ((TypeNode&Node)|Type|null) $type
     */
    public function __construct(
        Context $context,
        iterable $items,
        private Closure $type,
    ) {
        parent::__construct($context, $items);
    }

    protected function getPrefix(): string {
        return '(';
    }

    protected function getSuffix(): string {
        return ')';
    }

    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeArguments();
    }

    protected function isAlwaysMultiline(): bool {
        return parent::isAlwaysMultiline()
            || $this->getSettings()->isAlwaysMultilineArguments();
    }

    protected function block(string|int $key, mixed $item): Block {
        return new Argument(
            $this->getContext(),
            $item,
            ($this->type)($item),
        );
    }
}
