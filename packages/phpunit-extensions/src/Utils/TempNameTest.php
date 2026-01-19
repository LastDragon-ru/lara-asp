<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Utils;

use LastDragon_ru\PhpUnit\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(TempName::class)]
final class TempNameTest extends TestCase {
    public function testGetIterator(): void {
        $instance = new TempName();
        $variants = iterator_to_array($instance);

        self::assertCount($instance->count, $variants);
    }
}
