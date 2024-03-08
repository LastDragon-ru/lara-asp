<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Concerns;

use LastDragon_ru\LaraASP\Testing\Comparators\EloquentModelComparator;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\TestCase;

/**
 * Adds {@link EloquentModelComparator}
 *
 * @see EloquentModelComparator
 *
 * @phpstan-require-extends TestCase
 */
trait ModelComparator {
    /**
     * @internal
     */
    #[Before]
    protected function initModelComparator(): void {
        $this->registerComparator(new EloquentModelComparator());
    }
}
