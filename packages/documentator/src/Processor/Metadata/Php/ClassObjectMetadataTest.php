<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Php;

use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\WithProcessor;
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
        $metadata = $factory->resolve($file, ClassObject::class);

        self::assertSame(
            (new ReflectionClass($this::class))->getShortName(),
            (string) $metadata->class->name,
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
