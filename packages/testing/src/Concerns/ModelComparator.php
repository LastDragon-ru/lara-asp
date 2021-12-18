<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Concerns;

use LastDragon_ru\LaraASP\Testing\Comparators\EloquentModelComparator;
use PHPUnit\Framework\Test;

/**
 * Adds {@link \LastDragon_ru\LaraASP\Testing\Comparators\EloquentModelComparator}
 *
 * @mixin Test
 */
trait ModelComparator {
    /**
     * @before
     * @internal
     */
    public function initModelComparator(): void {
        $this->registerComparator(new EloquentModelComparator());
    }
}
