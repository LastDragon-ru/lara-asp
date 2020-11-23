<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Assertions;

use LastDragon_ru\LaraASP\Testing\Comparators\ScalarStrictComparator;

/**
 * Make `assertEquals` strict (the {@link \LastDragon_ru\LaraASP\Testing\SetUpTraits} is required).
 *
 * @mixin \PHPUnit\Framework\Test
 */
trait StrictAssertEquals {
    public function setUpStrictAssertEquals(): void {
        $this->registerComparator(new ScalarStrictComparator());
    }
}
