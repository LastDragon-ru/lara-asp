<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Comparators;

use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\ScalarComparator;
use SebastianBergmann\Exporter\Exporter;

class ScalarStrictComparator extends ScalarComparator {
    public function assertEquals(
        mixed $expected,
        mixed $actual,
        float $delta = 0.0,
        bool $canonicalize = false,
        bool $ignoreCase = false,
    ): void {
        if ($expected === $actual) {
            return;
        }

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
