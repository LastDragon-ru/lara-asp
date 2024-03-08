<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Comparators;

use Override;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\ScalarComparator;
use SebastianBergmann\Exporter\Exporter;

use function is_string;
use function mb_strtolower;

/**
 * Makes comparison of scalars strict.
 */
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
        // Ignore case?
        $actualNormalized   = $actual;
        $expectedNormalized = $expected;

        if ($ignoreCase) {
            if (is_string($actual)) {
                $actualNormalized = mb_strtolower($actual);
            }

            if (is_string($expected)) {
                $expectedNormalized = mb_strtolower($expected);
            }
        }

        // Same?
        if ($expectedNormalized === $actualNormalized) {
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
