<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Providers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ArrayDataProvider::class)]
class ArrayDataProviderTest extends TestCase {
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

        self::assertEquals($e, (new ArrayDataProvider($a))->getData());
    }

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

        self::assertEquals($e, (new ArrayDataProvider($a))->getData(true));
    }
}
