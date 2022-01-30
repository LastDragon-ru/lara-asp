<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\BlockSettings;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Named;

/**
 * @internal
 */
class DirectiveLocationBlock extends Block implements Named {
    public function __construct(
        BlockSettings $settings,
        int $level,
        int $used,
        private string $location,
    ) {
        parent::__construct($settings, $level, $used);
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
