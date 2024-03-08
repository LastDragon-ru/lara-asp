<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Concerns;

use LastDragon_ru\LaraASP\Testing\Comparators\DatabaseQueryComparator as Comparator;
use PHPUnit\Framework\TestCase;

/**
 * Adds {@link Comparator}
 *
 * @see Comparator
 *
 * @phpstan-require-extends TestCase
 */
trait DatabaseQueryComparator {
    /**
     * @before
     * @internal
     */
    public function initDatabaseQueryComparator(): void {
        $this->registerComparator(new Comparator());
    }
}
