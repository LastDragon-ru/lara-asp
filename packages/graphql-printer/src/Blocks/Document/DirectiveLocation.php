<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

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
        private string $location,
    ) {
        parent::__construct($context, $level, $used);
    }

    public function getName(): string {
        return $this->getLocation();
    }

    protected function getLocation(): string {
        return $this->location;
    }

    protected function content(): string {
        return $this->getLocation();
    }
}
