<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts\Serializer;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Caster;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\FileSystem\Content;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
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

        $file = Mockery::mock(File::class, [new FilePath("/file.{$extension}"), Mockery::mock(Caster::class)]);
        $file
            ->shouldReceive('as')
            ->with(Content::class)
            ->once()
            ->andReturn(new Content($content));

        $cast   = new SerializableCast($serializer);
        $actual = $cast->castTo($file, Serializable::class);

        self::assertSame($object, $actual);
    }

    public function testCastFrom(): void {
        $ext        = 'json';
        $file       = Mockery::mock(File::class, [new FilePath("/file.{$ext}"), Mockery::mock(Caster::class)]);
        $object     = Mockery::mock(Serializable::class);
        $content    = 'content';
        $serializer = Mockery::mock(Serializer::class);
        $serializer
            ->shouldReceive('serialize')
            ->with($object, $ext)
            ->once()
            ->andReturn($content);

        $cast   = new SerializableCast($serializer);
        $actual = $cast->castFrom($file, $object);

        self::assertSame($content, $actual);
    }
}
