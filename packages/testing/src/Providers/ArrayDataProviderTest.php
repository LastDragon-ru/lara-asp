<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Providers;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Testing\Providers\ArrayDataProvider
 */
class ArrayDataProviderTest extends TestCase {
    /**
     * @covers ::getData
     */
    public function testGetData(): void {
        $f = new ExpectedFinal('expected final');
        $a = [
            ['expected a', 'value a'],
            [$f, 'value final'],
        ];
        $e = [
            ['expected a', 'value a'],
            [$f->getValue(), 'value final'],
        ];

        $this->assertEquals($e, (new ArrayDataProvider($a))->getData());
    }

    /**
     * @covers ::getData
     */
    public function testGetDataRaw(): void {
        $f = new ExpectedFinal('expected final');
        $a = [
            ['expected a', 'value a'],
            [$f, 'value final'],
        ];
        $e = [
            ['expected a', 'value a'],
            [$f, 'value final'],
        ];

        $this->assertEquals($e, (new ArrayDataProvider($a))->getData(true));
    }
}
