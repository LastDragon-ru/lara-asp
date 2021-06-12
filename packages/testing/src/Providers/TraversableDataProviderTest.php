<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Providers;

use ArrayIterator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Testing\Providers\TraversableDataProvider
 */
class TraversableDataProviderTest extends TestCase {
    /**
     * @covers ::getData
     */
    public function testGetData(): void {
        $f = new ExpectedFinal('expected final');
        $a = new ArrayIterator([
            ['expected a', 'value a'],
            [$f, 'value final'],
        ]);
        $e = [
            ['expected a', 'value a'],
            [$f->getValue(), 'value final'],
        ];

        $this->assertEquals($e, (new TraversableDataProvider($a))->getData());
    }

    /**
     * @covers ::getData
     */
    public function testGetDataRaw(): void {
        $f = new ExpectedFinal('expected final');
        $a = new ArrayIterator([
            ['expected a', 'value a'],
            [$f, 'value final'],
        ]);
        $e = [
            ['expected a', 'value a'],
            [$f, 'value final'],
        ];

        $this->assertEquals($e, (new TraversableDataProvider($a))->getData(true));
    }
}
