<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Logger\Internals;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Logger\Enums\Flag;
use PHPUnit\Framework\Attributes\CoversClass;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(Statistics::class)]
final class StatisticsTest extends TestCase {
    public function testArrayAccess(): void {
        $statistics = new Statistics();
        $flag       = Flag::Write;
        $usage      = new Usage(1, 2, 3);

        self::assertFalse(isset($statistics[$flag]));

        $statistics[$flag] = $usage;

        self::assertTrue(isset($statistics[$flag]));
        self::assertSame($usage, $statistics[$flag]);

        unset($statistics[$flag]);

        self::assertFalse(isset($statistics[$flag]));
    }

    public function testIteratorAggregate(): void {
        $statistics = new Statistics();
        $aFlag      = Flag::Write;
        $aUsage     = new Usage(1, 2, 3);
        $bFlag      = Flag::Read;
        $bUsage     = new Usage(3, 2, 1);

        $statistics[$aFlag] = $aUsage;
        $statistics[$bFlag] = $bUsage;

        $keys   = [];
        $values = [];

        foreach ($statistics as $flag => $usage) {
            $keys[]   = $flag;
            $values[] = $usage;
        }

        self::assertEquals([$aFlag, $bFlag], $keys);
        self::assertEquals([$aUsage, $bUsage], $values);
    }

    public function testFlags(): void {
        $statistics = new Statistics();
        $aFlag      = Flag::Write;
        $aUsage     = new Usage(1, 2, 3);
        $bFlag      = Flag::Read;
        $bUsage     = new Usage(3, 2, 1);

        $statistics[$aFlag] = $aUsage;
        $statistics[$bFlag] = $bUsage;

        self::assertEquals([$aFlag, $bFlag], $statistics->flags());
    }

    public function testMerge(): void {
        $aStatistics             = new Statistics();
        $aStatistics[Flag::Read] = new Usage(1, 2, 3);

        $bStatistics              = new Statistics();
        $bStatistics[Flag::Write] = new Usage(3, 2, 1);
        $bStatistics->merge($aStatistics);
        $bStatistics->merge($aStatistics);

        $aUsages = iterator_to_array($aStatistics, false);
        $bUsages = iterator_to_array($bStatistics, false);

        self::assertEquals([new Usage(1, 2, 3)], $aUsages);
        self::assertEquals([new Usage(3, 2, 1), new Usage(2, 4, 6)], $bUsages);
    }
}
