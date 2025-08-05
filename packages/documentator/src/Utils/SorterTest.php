<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_reverse;
use function shuffle;
use function usort;

/**
 * @internal
 */
#[CoversClass(Sorter::class)]
final class SorterTest extends TestCase {
    public function testForString(): void {
        $sorter   = $this->app()->make(Sorter::class);
        $expected = [
            'as',
            'As',
            'às',
            'Às',
            'at',
            'b1',
            'b2',
            'b3b',
            'b100',
            'b200abc',
        ];

        // Ascending
        $ascending = $expected;

        shuffle($ascending);
        usort($ascending, $sorter->forString(SortOrder::Asc));

        self::assertEquals($expected, $ascending);

        // Descending
        $descending = $expected;

        shuffle($descending);
        usort($descending, $sorter->forString(SortOrder::Desc));

        self::assertEquals(array_reverse($expected), $descending);
    }

    public function testForVersion(): void {
        $sorter   = $this->app()->make(Sorter::class);
        $expected = [
            '1.1.1-beta.1',
            'v1.1.1-beta.2',
            '1.1.1-rc.1',
            '1.2.0',
            '1.2.1',
            'dev-main',
        ];

        // Ascending
        $ascending = $expected;

        shuffle($ascending);
        usort($ascending, $sorter->forVersion(SortOrder::Asc));

        self::assertEquals($expected, $ascending);

        // Descending
        $descending = $expected;

        shuffle($descending);
        usort($descending, $sorter->forVersion(SortOrder::Desc));

        self::assertEquals(array_reverse($expected), $descending);
    }
}
