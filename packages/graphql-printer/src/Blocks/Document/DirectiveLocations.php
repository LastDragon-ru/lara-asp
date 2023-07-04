<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\NameNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\UsageList;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

/**
 * @internal
 * @extends UsageList<DirectiveLocation, NameNode|string>
 */
class DirectiveLocations extends UsageList {
    /**
     * @inheritDoc
     */
    public function __construct(
        Context $context,
        int $level,
        int $used,
        iterable $items,
        bool $isAlwaysMultiline = false,
        private bool $repeatable = false,
    ) {
        parent::__construct($context, $level, $used, $items, $isAlwaysMultiline);
    }

    protected function block(int $level, int $used, mixed $item): Block {
        return new DirectiveLocation(
            $this->getContext(),
            $level + 1,
            $used,
            $item,
        );
    }

    protected function separator(): string {
        return '|';
    }

    protected function prefix(): string {
        return ($this->repeatable ? "repeatable{$this->space()}" : '').'on';
    }

    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeDirectiveLocations();
    }

    protected function isAlwaysMultiline(): bool {
        return parent::isAlwaysMultiline()
            || $this->getSettings()->isAlwaysMultilineDirectiveLocations();
    }
}
