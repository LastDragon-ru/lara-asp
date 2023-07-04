<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\NameNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\NamedBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;

/**
 * @internal
 */
class DirectiveLocation extends Block implements NamedBlock {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        private NameNode|string $location,
    ) {
        parent::__construct($context, $level, $used);
    }

    public function getName(): string {
        return $this->getLocation();
    }

    protected function content(int $level, int $used): string {
        return $this->getLocation();
    }

    private function getLocation(): string {
        return $this->location instanceof NameNode
            ? $this->location->value
            : $this->location;
    }
}
