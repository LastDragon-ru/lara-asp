<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Comparators;

use PHPUnit\Framework\TestCase;
use SebastianBergmann\Comparator\ComparisonFailure;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Testing\Comparators\ScalarStrictComparator
 */
class ScalarStrictComparatorTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @covers ::assertEquals
     *
     * @dataProvider dataProviderAssertEquals
     */
    public function testAssertEquals(bool $equals, mixed $expected, mixed $actual): void {
        if ($equals) {
            $this->assertTrue(true);
        } else {
            $this->expectException(ComparisonFailure::class);
        }

        (new ScalarStrictComparator())->assertEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    public function dataProviderAssertEquals(): array {
        return [
            'int'         => [true, 1, 1],
            'bool'        => [true, true, true],
            'weird php 1' => [false, '', false],
            'weird php 2' => [false, null, false],
            'weird php 3' => [false, 0, false],
            'weird php 4' => [false, 0, null],
            'weird php 5' => [false, null, ''],
        ];
    }
    // </editor-fold>
}
