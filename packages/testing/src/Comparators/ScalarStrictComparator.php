<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Comparators;

use Override;
use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Exporter\Exporter;

use function is_scalar;
use function is_string;
use function mb_strtolower;

/**
 * Makes comparison of scalars strict.
 */
class ScalarStrictComparator extends Comparator {
    #[Override]
    public function accepts(mixed $expected, mixed $actual): bool {
        return is_scalar($expected)
            && is_scalar($actual);
    }

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
            $expected,
            $actual,
            $exporter->export($expected),
            $exporter->export($actual),
            'Failed asserting that two values are equal.',
        );
    }
}
