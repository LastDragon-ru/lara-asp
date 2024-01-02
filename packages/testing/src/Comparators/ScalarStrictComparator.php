<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Comparators;

use Override;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\ScalarComparator;
use SebastianBergmann\Exporter\Exporter;

class ScalarStrictComparator extends ScalarComparator {
    /**
     * @param array<array-key, mixed> $processed
     */
    #[Override]
    public function assertEquals(
        mixed $expected,
        mixed $actual,
        float $delta = 0.0,
        bool $canonicalize = false,
        bool $ignoreCase = false,
        array &$processed = [],
    ): void {
        // Same?
        if ($expected === $actual) {
            return;
        }

        // Nope
        $exporter = new Exporter();

        throw new ComparisonFailure(
            expected        : $expected,
            actual          : $actual,
            expectedAsString: $exporter->export($expected),
            actualAsString  : $exporter->export($actual),
            message         : 'Failed asserting that two values are equal.',
        );
    }
}
