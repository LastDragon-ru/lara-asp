<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Providers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(MergeDataProvider::class)]
class MergeDataProviderTest extends TestCase {
    public function testGetData(): void {
        $f = new ExpectedFinal('expected final');
        $a = [
            'a' => ['expected a', 'value a'],
            'b' => ['expected b', 'value b'],
        ];
        $b = [
            'a' => ['expected b', 'value b'],
            'b' => ['expected c', 'value c'],
        ];
        $c = [
            ['expected d', 'value d'],
            [$f, 'value e'],
        ];
        $e = [
            'a / a' => ['expected a', 'value a'],
            'a / b' => ['expected b', 'value b'],
            'b / a' => ['expected b', 'value b'],
            'b / b' => ['expected c', 'value c'],
            '0 / 0' => ['expected d', 'value d'],
            '0 / 1' => [$f->getValue(), 'value e'],
        ];

        self::assertEquals($e, (new MergeDataProvider([
            'a' => new ArrayDataProvider($a),
            'b' => new ArrayDataProvider($b),
            0   => new ArrayDataProvider($c),
        ]))->getData());
    }

    public function testGetDataRaw(): void {
        $f = new ExpectedFinal('expected final');
        $a = [
            'a' => ['expected a', 'value a'],
            'b' => ['expected b', 'value b'],
        ];
        $b = [
            'a' => ['expected b', 'value b'],
            'b' => [$f, 'value c'],
        ];
        $e = [
            'a / a' => ['expected a', 'value a'],
            'a / b' => ['expected b', 'value b'],
            'b / a' => ['expected b', 'value b'],
            'b / b' => [$f, 'value c'],
        ];

        self::assertEquals($e, (new MergeDataProvider([
            'a' => new ArrayDataProvider($a),
            'b' => new ArrayDataProvider($b),
        ]))->getData(true));
    }
}
