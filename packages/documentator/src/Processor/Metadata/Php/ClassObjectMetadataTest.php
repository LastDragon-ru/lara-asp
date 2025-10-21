<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Php;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Package\WithProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;

/**
 * @internal
 */
#[CoversClass(ClassObjectMetadata::class)]
final class ClassObjectMetadataTest extends TestCase {
    use WithProcessor;

    public function testResolve(): void {
        $fs       = $this->getFileSystem(__DIR__);
        $file     = $fs->getFile(__FILE__);
        $factory  = new ClassObjectMetadata();
        $resolved = $factory->resolve($file, ClassObject::class);

        self::assertSame(
            (new ReflectionClass($this::class))->getShortName(),
            (string) $resolved->class->name,
        );
    }

    public function testResolveNotFound(): void {
        self::expectException(ClassObjectNotFound::class);

        $fs      = $this->getFileSystem(__DIR__);
        $file    = $fs->getFile('../../../../README.md');
        $factory = new ClassObjectMetadata();

        $factory->resolve($file, ClassObject::class);
    }
}
