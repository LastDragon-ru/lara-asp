<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Concerns;

use LastDragon_ru\LaraASP\Testing\Comparators\ScalarStrictComparator;

/**
 * Makes `assertEquals` strict.
 *
 * @required {@link \LastDragon_ru\LaraASP\Testing\SetUpTraits}
 *
 * @mixin \PHPUnit\Framework\Test
 */
trait StrictAssertEquals {
    public function setUpStrictAssertEquals(): void {
        $this->registerComparator(new ScalarStrictComparator());
    }
}
