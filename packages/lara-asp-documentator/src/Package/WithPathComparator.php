<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Package;

use LastDragon_ru\Path\Path;
use Override;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Util\Exporter;
use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Comparator\ComparisonFailure;

/**
 * @phpstan-require-extends TestCase
 */
trait WithPathComparator {
    #[Before]
    public function initPathComparator(): void {
        $this->registerComparator(
            new class() extends Comparator {
                #[Override]
                public function accepts(mixed $expected, mixed $actual): bool {
                    return $expected instanceof Path && $actual instanceof Path;
                }

                #[Override]
                public function assertEquals(
                    mixed $expected,
                    mixed $actual,
                    float $delta = 0.0,
                    bool $canonicalize = false,
                    bool $ignoreCase = false,
                ): void {
                    // Same?
                    if ($expected instanceof Path && $actual instanceof Path && $expected->equals($actual)) {
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
            },
        );
    }
}
