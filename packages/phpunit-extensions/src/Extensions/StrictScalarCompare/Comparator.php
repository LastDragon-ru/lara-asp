<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Extensions\StrictScalarCompare;

use Override;
use PHPUnit\Framework\Assert;
use PHPUnit\Util\Exporter;
use SebastianBergmann\Comparator\Comparator as AbstractComparator;
use SebastianBergmann\Comparator\ComparisonFailure;

use function is_scalar;
use function is_string;
use function mb_strtolower;

/**
 * By default, the {@see Assert::assertEquals()} uses weak comparison (`==`).
 * Probably this is not what you want nowadays. This comparator uses `===` to
 * compare scalars.
 *
 * @see Assert
 */
class Comparator extends AbstractComparator {
    #[Override]
    public function accepts(mixed $expected, mixed $actual): bool {
        return is_scalar($expected)
            && is_scalar($actual);
    }

    #[Override]
    public function assertEquals(
        mixed $expected,
        mixed $actual,
        float $delta = 0.0,
        bool $canonicalize = false,
        bool $ignoreCase = false,
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
        throw new ComparisonFailure(
            $expected,
            $actual,
            Exporter::export($expected),
            Exporter::export($actual),
            'Failed asserting that two values are equal.',
        );
    }
}
