<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;

/**
 * @internal
 */
#[CoversClass(ClassObjectCast::class)]
final class ClassObjectCastTest extends TestCase {
    use WithProcessor;

    public function testCastTo(): void {
        $fs     = $this->getFileSystem(__DIR__);
        $file   = $fs->getFile(__FILE__);
        $cast   = new ClassObjectCast();
        $casted = $cast->castTo($file, ClassObject::class);

        self::assertNotNull($casted);
        self::assertSame(
            (new ReflectionClass($this::class))->getShortName(),
            (string) $casted->class->name,
        );
    }

    public function testCastToNotFound(): void {
        $fs     = $this->getFileSystem(__DIR__);
        $file   = $fs->getFile('../../../../README.md');
        $cast   = new ClassObjectCast();
        $casted = $cast->castTo($file, ClassObject::class);

        self::assertNull($casted);
    }
}
