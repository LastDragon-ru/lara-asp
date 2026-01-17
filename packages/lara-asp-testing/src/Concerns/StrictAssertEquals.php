<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Concerns;

use LastDragon_ru\PhpUnit\Extensions\StrictScalarCompare\Comparator;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\TestCase;

/**
 * Makes `assertEquals` strict.
 *
 * @phpstan-require-extends TestCase
 *
 * @deprecated 10.0.0 Please use `\LastDragon_ru\PhpUnit\Extensions\StrictScalarCompare\Extension` instead.
 */
trait StrictAssertEquals {
    /**
     * @internal
     */
    #[Before]
    protected function initStrictAssertEquals(): void {
        $this->registerComparator(new Comparator());
    }
}
