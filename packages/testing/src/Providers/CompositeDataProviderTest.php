<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Providers;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Testing\Providers\CompositeDataProvider
 */
class CompositeDataProviderTest extends TestCase {
    /**
     * @covers ::getData
     */
    public function testGetData(): void {
        $a = [
            ['expected a', 'value a'],
            [new ExpectedFinal('expected final'), 'value final'],
        ];
        $b = [
            ['expected b', 'value b'],
            ['expected c', 'value c'],
        ];
        $c = [
            ['expected d', 'value d'],
            [new ExpectedValue('expected e'), 'value e'],
        ];
        $e = [
            '0 / 0 / 0' => ['expected d', 'value a', 'value b', 'value d'],
            '0 / 0 / 1' => ['expected e', 'value a', 'value b', 'value e'],
            '0 / 1 / 0' => ['expected d', 'value a', 'value c', 'value d'],
            '0 / 1 / 1' => ['expected e', 'value a', 'value c', 'value e'],
            '1'         => ['expected final', 'value final'],
        ];

        $this->assertEquals($e, (new CompositeDataProvider(
            new ArrayDataProvider($a),
            new ArrayDataProvider($b),
            new ArrayDataProvider($c),
        ))->getData());
    }

    /**
     * @covers ::getData
     */
    public function testGetDataRaw(): void {
        $f = new ExpectedFinal('expected final');
        $u = new UnknownValue();
        $a = [
            [$u, 'value a'],
            [$f, 'value final'],
        ];
        $b = [
            ['expected b', 'value b'],
            [$u, 'value c'],
        ];
        $e = [
            '0 / 0' => ['expected b', 'value a', 'value b'],
            '0 / 1' => [$u, 'value a', 'value c'],
            '1'     => [$f, 'value final'],
        ];

        $this->assertEquals($e, (new CompositeDataProvider(
            new ArrayDataProvider($a),
            new ArrayDataProvider($b),
        ))->getData(true));
    }

    /**
     * @covers ::getData
     */
    public function testGetDataSingleProviderPassed(): void {
        $a = [
            ['expected a', 'value a'],
            [new ExpectedFinal('expected final'), 'value final'],
        ];
        $e = [
            ['expected a', 'value a'],
            ['expected final', 'value final'],
        ];

        $this->assertEquals($e, (new CompositeDataProvider(
            new ArrayDataProvider($a),
        ))->getData());
    }
}
