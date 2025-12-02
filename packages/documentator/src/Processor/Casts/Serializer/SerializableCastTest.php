<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts\Serializer;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Caster;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
use LastDragon_ru\Path\FilePath;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(SerializableCast::class)]
final class SerializableCastTest extends TestCase {
    public function testCastTo(): void {
        $object     = Mockery::mock(Serializable::class);
        $content    = 'content';
        $extension  = 'json';
        $serializer = Mockery::mock(Serializer::class);
        $serializer
            ->shouldReceive('deserialize')
            ->with(Serializable::class, $content, $extension)
            ->once()
            ->andReturn($object);

        $filesystem = Mockery::mock(FileSystem::class);
        $caster     = Mockery::mock(Caster::class);
        $file       = Mockery::mock(File::class, [$filesystem, new FilePath("/file.{$extension}"), $caster]);

        $filesystem
            ->shouldReceive('read')
            ->with($file)
            ->once()
            ->andReturn($content);

        $cast   = new SerializableCast($serializer);
        $actual = $cast->castTo($file, Serializable::class);

        self::assertSame($object, $actual);
    }

    public function testCastFrom(): void {
        $extension  = 'json';
        $filesystem = Mockery::mock(FileSystem::class);
        $caster     = Mockery::mock(Caster::class);
        $file       = Mockery::mock(File::class, [$filesystem, new FilePath("/file.{$extension}"), $caster]);
        $object     = Mockery::mock(Serializable::class);
        $content    = 'content';
        $serializer = Mockery::mock(Serializer::class);
        $serializer
            ->shouldReceive('serialize')
            ->with($object, $extension)
            ->once()
            ->andReturn($content);

        $cast   = new SerializableCast($serializer);
        $actual = $cast->castFrom($file, $object);

        self::assertSame($content, $actual);
    }
}
