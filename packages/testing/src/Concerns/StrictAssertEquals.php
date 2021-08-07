<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Concerns;

use LastDragon_ru\LaraASP\Testing\Comparators\ScalarStrictComparator;
use PHPUnit\Framework\Test;

/**
 * Makes `assertEquals` strict.
 *
 * @required {@link \LastDragon_ru\LaraASP\Testing\SetUpTraits}
 *
 * @mixin Test
 */
trait StrictAssertEquals {
    public function setUpStrictAssertEquals(): void {
        $this->registerComparator(new ScalarStrictComparator());
    }
}
