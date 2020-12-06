<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions;

use LastDragon_ru\LaraASP\Testing\Comparators\EloquentModelComparator;

/**
 * Adds {@link \LastDragon_ru\LaraASP\Testing\Comparators\EloquentModelComparator}
 *
 * @required {@link \LastDragon_ru\LaraASP\Testing\SetUpTraits}
 *
 * @mixin \PHPUnit\Framework\Test
 */
trait ModelComparator {
    public function setUpModelComparator(): void {
        $this->registerComparator(new EloquentModelComparator());
    }
}
