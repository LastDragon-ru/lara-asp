<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Concerns;

use LastDragon_ru\LaraASP\Testing\Comparators\ScalarStrictComparator;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\TestCase;

/**
 * Makes `assertEquals` strict.
 *
 * @phpstan-require-extends TestCase
 */
trait StrictAssertEquals {
    /**
     * @internal
     */
    #[Before]
    protected function initStrictAssertEquals(): void {
        $this->registerComparator(new ScalarStrictComparator());
    }
}
