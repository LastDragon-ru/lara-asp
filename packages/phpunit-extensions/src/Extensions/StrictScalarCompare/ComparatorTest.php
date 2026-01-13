<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Extensions\StrictScalarCompare;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Comparator\ComparisonFailure;

/**
 * @internal
 */
#[CoversClass(Comparator::class)]
final class ComparatorTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderAssertEquals')]
    public function testAssertEquals(bool $equals, mixed $expected, mixed $actual): void {
        if (!$equals) {
            self::expectException(ComparisonFailure::class);
        }

        (new Comparator())->assertEquals($expected, $actual);

        self::assertEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<array-key, mixed>
     */
    public static function dataProviderAssertEquals(): array {
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
