<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Hooks;

use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\MetadataUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\WithProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

/**
 * @internal
 */
#[CoversClass(Hooks::class)]
final class HooksTest extends TestCase {
    use WithProcessor;

    public function testGet(): void {
        self::expectException(MetadataUnresolvable::class);

        $meta  = new stdClass();
        $hooks = new Hooks([$meta]);
        $file  = $hooks->get($this->getFileSystem(__DIR__), Hook::Before);

        $file->as(stdClass::class);
    }

    public function testGetContext(): void {
        $meta   = new class(__FUNCTION__) extends stdClass {
            public function __construct(
                public string $value,
            ) {
                // empty
            }
        };
        $hooks  = new Hooks([$meta]);
        $file   = $hooks->get($this->getFileSystem(__DIR__), Hook::Context);
        $actual = $file->as(stdClass::class);

        self::assertSame(__FUNCTION__, $actual->value);
    }
}
