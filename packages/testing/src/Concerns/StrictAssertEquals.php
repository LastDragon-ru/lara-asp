<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Concerns;

use LastDragon_ru\LaraASP\Testing\Comparators\ScalarStrictComparator;
use PHPUnit\Framework\Test;

/**
 * Makes `assertEquals` strict.
 *
 * @mixin Test
 */
trait StrictAssertEquals {
    /**
     * @before
     * @internal
     */
    public function initStrictAssertEquals(): void {
        $this->registerComparator(new ScalarStrictComparator());
    }
}
