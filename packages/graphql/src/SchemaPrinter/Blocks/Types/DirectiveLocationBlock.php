<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Types;

use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks\Named;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc\PrinterSettings;

/**
 * @internal
 */
class DirectiveLocationBlock extends Block implements Named {
    public function __construct(
        PrinterSettings $settings,
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
