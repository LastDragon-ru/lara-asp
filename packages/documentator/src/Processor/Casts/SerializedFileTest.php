<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
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
#[CoversClass(SerializedFile::class)]
final class SerializedFileTest extends TestCase {
    public function testTo(): void {
        $fs         = Mockery::mock(FileSystem::class);
        $path       = new FilePath('/file.md');
        $file       = new File($fs, Mockery::mock(Caster::class), $path);
        $content    = 'content';
        $serializer = Mockery::mock(Serializer::class);
        $serialized = new SerializedFile($serializer, $file);

        $fs
            ->shouldReceive('read')
            ->with($file)
            ->twice()
            ->andReturn($content);

        $serializer
            ->shouldReceive('deserialize')
            ->once()
            ->with(SerializedFileTest__SerializableA::class, $content, $path->extension)
            ->andReturn(
                new SerializedFileTest__SerializableA(),
            );
        $serializer
            ->shouldReceive('deserialize')
            ->once()
            ->with(SerializedFileTest__SerializableB::class, $content, $path->extension)
            ->andReturn(
                new SerializedFileTest__SerializableB(),
            );

        self::assertSame(
            $serialized->to(SerializedFileTest__SerializableA::class),
            $serialized->to(SerializedFileTest__SerializableA::class),
        );

        /** @phpstan-ignore staticMethod.alreadyNarrowedType (for test) */
        self::assertNotSame(
            $serialized->to(SerializedFileTest__SerializableA::class),
            $serialized->to(SerializedFileTest__SerializableB::class),
        );
    }

    public function testToString(): void {
        $fs         = Mockery::mock(FileSystem::class);
        $path       = new FilePath('/file.md');
        $file       = new File($fs, Mockery::mock(Caster::class), $path);
        $object     = new SerializedFileTest__SerializableA();
        $content    = 'content';
        $serializer = Mockery::mock(Serializer::class);
        $serialized = new SerializedFile($serializer, $file);

        $fs
            ->shouldReceive('read')
            ->with($file)
            ->once()
            ->andReturn($content);

        $serializer
            ->shouldReceive('deserialize')
            ->once()
            ->with(SerializedFileTest__SerializableA::class, $content, $path->extension)
            ->andReturn($object);
        $serializer
            ->shouldReceive('serialize')
            ->once()
            ->with($object, $path->extension)
            ->andReturn($content);

        $value = $serialized->to(SerializedFileTest__SerializableA::class);

        self::assertSame($content, $serialized->toString($object));
        self::assertSame($value, $serialized->to(SerializedFileTest__SerializableA::class));
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class SerializedFileTest__SerializableA implements Serializable {
    // empty
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class SerializedFileTest__SerializableB implements Serializable {
    // empty
}
