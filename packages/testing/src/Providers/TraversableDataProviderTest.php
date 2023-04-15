<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Providers;

use ArrayIterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TraversableDataProvider::class)]
class TraversableDataProviderTest extends TestCase {
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

        self::assertEquals($e, (new TraversableDataProvider($a))->getData());
    }

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

        self::assertEquals($e, (new TraversableDataProvider($a))->getData(true));
    }
}
