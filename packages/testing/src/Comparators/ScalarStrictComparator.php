<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Comparators;

use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\ScalarComparator;
use SebastianBergmann\Exporter\Exporter;

use function assert;
use function is_bool;
use function is_float;

class ScalarStrictComparator extends ScalarComparator {
    public function assertEquals(
        mixed $expected,
        mixed $actual,
        mixed $delta = 0.0,
        mixed $canonicalize = false,
        mixed $ignoreCase = false,
    ): void {
        // todo(testing): Update method signature after PHPUnit v9.5 removal.
        assert(is_float($delta));
        assert(is_bool($canonicalize));
        assert(is_bool($ignoreCase));

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
