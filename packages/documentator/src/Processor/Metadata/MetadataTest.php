<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

use Illuminate\Contracts\Container\Container;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\MetadataResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\MetadataSerializer;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\FileMetadataUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\MetadataUnserializable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use RuntimeException;
use stdClass;
use UnexpectedValueException;

use function assert;
use function is_a;
use function sprintf;

/**
 * @internal
 */
#[CoversClass(Metadata::class)]
final class MetadataTest extends TestCase {
    public function testGet(): void {
        // Prepare
        $aResolver = new MetadataTest__Resolver();
        $bResolver = new class() extends MetadataTest__Resolver {
            /**
             * @inheritDoc
             */
            #[Override]
            public static function getExtensions(): array {
                return ['extension-b'];
            }
        };
        $container = Mockery::mock(Container::class);
        $resolver  = Mockery::mock(ContainerResolver::class);
        $resolver
            ->shouldReceive('getInstance')
            ->once()
            ->andReturn($container);
        $container
            ->shouldReceive('make')
            ->with($bResolver::class)
            ->once()
            ->andReturn($bResolver);

        $metadata = new class($resolver) extends Metadata {
            #[Override]
            protected function addBuiltInResolvers(): void {
                // empty
            }
        };

        $metadata->addResolver($aResolver, 100);
        $metadata->addResolver($bResolver::class, 200);

        // Wildcard
        $aFile = Mockery::mock(File::class);
        $aFile
            ->shouldReceive('getExtension')
            ->once()
            ->andReturn('extension-a');

        $aActual = $metadata->get($aFile, MetadataTest__Value::class);

        self::assertEquals(new MetadataTest__Value($aResolver::class), $aActual);
        self::assertSame($aActual, $metadata->get($aFile, MetadataTest__Value::class));

        // Extension
        $bFile = Mockery::mock(File::class);
        $bFile
            ->shouldReceive('getExtension')
            ->once()
            ->andReturn('extension-b');
        $bActual = $metadata->get($bFile, MetadataTest__Value::class);

        self::assertEquals(new MetadataTest__Value($bResolver::class), $bActual);
    }

    public function testGetUnexpected(): void {
        // Prepare
        $resolver = Mockery::mock(MetadataTest__Resolver::class);
        $resolver->makePartial();
        $resolver
            ->shouldReceive('resolve')
            ->once()
            ->andReturn(new stdClass());

        $file = Mockery::mock(File::class);
        $file
            ->shouldReceive('getExtension')
            ->once()
            ->andReturn('extension');

        $container = Mockery::mock(ContainerResolver::class);
        $metadata  = new class($container) extends Metadata {
            #[Override]
            protected function addBuiltInResolvers(): void {
                // empty
            }
        };

        $metadata->addResolver($resolver);

        // Test
        $previous = null;

        try {
            $metadata->get($file, MetadataTest__Value::class);
        } catch (FileMetadataUnresolvable $exception) {
            $previous = $exception->getPrevious();
        }

        self::assertInstanceOf(UnexpectedValueException::class, $previous);
        self::assertSame(
            sprintf(
                'Expected `%s`, got `%s` (resolver `%s`).',
                MetadataTest__Value::class,
                stdClass::class,
                $resolver::class,
            ),
            $previous->getMessage(),
        );
    }

    public function testGetNoResolver(): void {
        // Prepare
        $container = Mockery::mock(ContainerResolver::class);
        $metadata  = new class($container) extends Metadata {
            #[Override]
            protected function addBuiltInResolvers(): void {
                // empty
            }
        };
        $file      = Mockery::mock(File::class);
        $file
            ->shouldReceive('getExtension')
            ->once()
            ->andReturn('extension');

        // Test
        $previous = null;

        try {
            $metadata->get($file, MetadataTest__Value::class);
        } catch (FileMetadataUnresolvable $exception) {
            $previous = $exception->getPrevious();
        }

        self::assertInstanceOf(RuntimeException::class, $previous);
        self::assertSame('Resolver not found.', $previous->getMessage());
    }

    public function testHas(): void {
        $container = Mockery::mock(ContainerResolver::class);
        $metadata  = new Metadata($container);
        $file      = Mockery::mock(File::class);
        $value     = new MetadataTest__Value(__METHOD__);

        self::assertFalse($metadata->has($file, MetadataTest__Value::class));

        $metadata->set($file, $value);

        self::assertTrue($metadata->has($file, MetadataTest__Value::class));
    }

    public function testSet(): void {
        $container = Mockery::mock(ContainerResolver::class);
        $metadata  = new Metadata($container);
        $file      = Mockery::mock(File::class);
        $value     = new MetadataTest__Value(__METHOD__);

        $metadata->set($file, $value);

        self::assertSame($value, $metadata->get($file, MetadataTest__Value::class));
    }

    public function testReset(): void {
        $container = Mockery::mock(ContainerResolver::class);
        $metadata  = new Metadata($container);
        $file      = Mockery::mock(File::class);
        $value     = new MetadataTest__Value(__METHOD__);

        self::assertFalse($metadata->has($file, MetadataTest__Value::class));

        $metadata->set($file, $value);

        self::assertTrue($metadata->has($file, MetadataTest__Value::class));

        $metadata->reset($file);

        self::assertFalse($metadata->has($file, MetadataTest__Value::class));
    }

    public function testSerialize(): void {
        // Prepare
        $path      = new FilePath(__FILE__);
        $value     = new MetadataTest__Value(__METHOD__);
        $resolver  = new class() extends MetadataTest__Resolver implements MetadataSerializer {
            #[Override]
            public function serialize(FilePath $path, object $value): string {
                assert($value instanceof MetadataTest__Value);

                return $value->value;
            }
        };
        $container = Mockery::mock(ContainerResolver::class);
        $metadata  = new class($container) extends Metadata {
            #[Override]
            protected function addBuiltInResolvers(): void {
                // empty
            }
        };

        $metadata->addResolver($resolver);

        // Test
        self::assertSame($value->value, $metadata->serialize($path, $value));
    }

    public function testSerializeNoResolver(): void {
        // Prepare
        $path      = new FilePath(__FILE__);
        $value     = new MetadataTest__Value(__METHOD__);
        $container = Mockery::mock(ContainerResolver::class);
        $metadata  = new class($container) extends Metadata {
            #[Override]
            protected function addBuiltInResolvers(): void {
                // empty
            }
        };

        // Test
        $previous = null;

        try {
            $metadata->serialize($path, $value);
        } catch (MetadataUnserializable $exception) {
            $previous = $exception->getPrevious();
        }

        self::assertInstanceOf(RuntimeException::class, $previous);
        self::assertSame('Resolver not found.', $previous->getMessage());
    }

    public function testSerializeNoSerializer(): void {
        // Prepare
        $path      = new FilePath(__FILE__);
        $value     = new MetadataTest__Value(__METHOD__);
        $container = Mockery::mock(ContainerResolver::class);
        $metadata  = new class($container) extends Metadata {
            #[Override]
            protected function addBuiltInResolvers(): void {
                // empty
            }
        };

        $metadata->addResolver(new MetadataTest__Resolver());

        // Test
        $previous = null;

        try {
            $metadata->serialize($path, $value);
        } catch (MetadataUnserializable $exception) {
            $previous = $exception->getPrevious();
        }

        self::assertInstanceOf(RuntimeException::class, $previous);
        self::assertSame('Serializer not found.', $previous->getMessage());
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 * @implements MetadataResolver<MetadataTest__Value>
 */
class MetadataTest__Resolver implements MetadataResolver {
    /**
     * @inheritDoc
     */
    #[Override]
    public static function getExtensions(): array {
        return ['*'];
    }

    #[Override]
    public function isSupported(string $metadata): bool {
        return is_a($metadata, MetadataTest__Value::class, true);
    }

    #[Override]
    public function resolve(File $file, string $metadata): mixed {
        return new MetadataTest__Value($this::class);
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class MetadataTest__Value {
    public function __construct(
        public readonly string $value,
    ) {
        // empty
    }
}
