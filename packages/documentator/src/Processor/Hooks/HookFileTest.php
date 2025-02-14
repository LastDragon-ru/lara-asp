<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Hooks;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\MetadataUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

/**
 * @internal
 */
#[CoversClass(HookFile::class)]
final class HookFileTest extends TestCase {
    public function testAs(): void {
        $meta = new class(__FUNCTION__) extends stdClass {
            public function __construct(
                public string $value,
            ) {
                // empty
            }
        };
        $path = new FilePath(__FILE__);
        $file = new HookFile($path, [$meta]);

        $actual = $file->as(stdClass::class);

        self::assertSame(__FUNCTION__, $actual->value);
    }

    public function testAsUnresolvable(): void {
        self::expectException(MetadataUnresolvable::class);

        (new HookFile(new FilePath(__FILE__)))->as(stdClass::class);
    }
}
