<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Concerns;

use LastDragon_ru\LaraASP\Testing\Comparators\EloquentModelComparator;
use PHPUnit\Framework\Test;

/**
 * Adds {@link \LastDragon_ru\LaraASP\Testing\Comparators\EloquentModelComparator}
 *
 * @required {@link \LastDragon_ru\LaraASP\Testing\SetUpTraits}
 *
 * @mixin Test
 */
trait ModelComparator {
    public function setUpModelComparator(): void {
        $this->registerComparator(new EloquentModelComparator());
    }
}
