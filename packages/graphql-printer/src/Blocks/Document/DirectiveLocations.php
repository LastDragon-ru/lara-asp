<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\NameNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\UsageList;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use Override;

/**
 * @internal
 * @extends UsageList<DirectiveLocation, array-key, NameNode|string>
 */
class DirectiveLocations extends UsageList {
    /**
     * @inheritDoc
     */
    public function __construct(
        Context $context,
        iterable $items,
        bool $isAlwaysMultiline = false,
        private bool $repeatable = false,
    ) {
        parent::__construct($context, $items, $isAlwaysMultiline);
    }

    #[Override]
    protected function block(string|int $key, mixed $item): Block {
        return new DirectiveLocation(
            $this->getContext(),
            $item,
        );
    }

    #[Override]
    protected function separator(): string {
        return '|';
    }

    #[Override]
    protected function prefix(): string {
        return ($this->repeatable ? "repeatable{$this->space()}" : '').'on';
    }

    #[Override]
    protected function isNormalized(): bool {
        return $this->getSettings()->isNormalizeDirectiveLocations();
    }

    #[Override]
    protected function isAlwaysMultiline(): bool {
        return parent::isAlwaysMultiline()
            || $this->getSettings()->isAlwaysMultilineDirectiveLocations();
    }
}
