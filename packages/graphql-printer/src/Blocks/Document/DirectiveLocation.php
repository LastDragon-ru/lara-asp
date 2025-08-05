<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\NameNode;
use LastDragon_ru\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\GraphQLPrinter\Blocks\NamedBlock;
use LastDragon_ru\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use Override;

/**
 * @internal
 */
class DirectiveLocation extends Block implements NamedBlock {
    public function __construct(
        Context $context,
        private NameNode|string $location,
    ) {
        parent::__construct($context);
    }

    #[Override]
    public function getName(): string {
        return $this->getLocation();
    }

    #[Override]
    protected function content(Collector $collector, int $level, int $used): string {
        return $this->getLocation();
    }

    private function getLocation(): string {
        return $this->location instanceof NameNode
            ? $this->location->value
            : $this->location;
    }
}
