<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Concerns;

use LastDragon_ru\LaraASP\Testing\Comparators\DatabaseQueryComparator as Comparator;
use PHPUnit\Framework\Test;

/**
 * Adds {@link \LastDragon_ru\LaraASP\Testing\Comparators\DatabaseQueryComparator}
 *
 * @required {@link \LastDragon_ru\LaraASP\Testing\SetUpTraits}
 *
 * @mixin Test
 */
trait DatabaseQueryComparator {
    public function setUpDatabaseQueryComparator(): void {
        $this->registerComparator(new Comparator());
    }
}
