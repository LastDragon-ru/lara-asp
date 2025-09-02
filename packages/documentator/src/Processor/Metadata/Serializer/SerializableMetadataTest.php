<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata\Serializer;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\FileSystem\Content;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(SerializableMetadata::class)]
final class SerializableMetadataTest extends TestCase {
    public function testResolve(): void {
        $object     = Mockery::mock(Serializable::class);
        $content    = 'content';
        $extension  = 'json';
        $serializer = Mockery::mock(Serializer::class);
        $serializer
            ->shouldReceive('deserialize')
            ->with(Serializable::class, $content, $extension)
            ->once()
            ->andReturn($object);

        $file = Mockery::mock(File::class);
        $file
            ->shouldReceive('getExtension')
            ->once()
            ->andReturn($extension);
        $file
            ->shouldReceive('as')
            ->with(Content::class)
            ->once()
            ->andReturn(new Content($content));

        $metadata = new SerializableMetadata($serializer);
        $actual   = $metadata->resolve($file, Serializable::class);

        self::assertSame($object, $actual);
    }

    public function testSerialize(): void {
        $ext  = 'json';
        $file = Mockery::mock(File::class);
        $file
            ->shouldReceive('getExtension')
            ->once()
            ->andReturn($ext);

        $object     = Mockery::mock(Serializable::class);
        $content    = 'content';
        $serializer = Mockery::mock(Serializer::class);
        $serializer
            ->shouldReceive('serialize')
            ->with($object, $ext)
            ->once()
            ->andReturn($content);

        $metadata = new SerializableMetadata($serializer);
        $actual   = $metadata->serialize($file, $object);

        self::assertSame($content, $actual);
    }
}
