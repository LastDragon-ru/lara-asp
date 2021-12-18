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
     * @after
     * @internal
     */
    public function initStrictAssertEquals(): void {
        $this->registerComparator(new ScalarStrictComparator());
    }
}
