<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\NameNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\NamedBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

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

    public function getName(): string {
        return $this->getLocation();
    }

    protected function content(Collector $collector, int $level, int $used): string {
        return $this->getLocation();
    }

    private function getLocation(): string {
        return $this->location instanceof NameNode
            ? $this->location->value
            : $this->location;
    }
}
