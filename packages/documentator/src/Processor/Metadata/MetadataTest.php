<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Metadata;

use Illuminate\Contracts\Container\Container;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\MetadataResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\MetadataUnresolvable;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\MetadataUnserializable;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use RuntimeException;
use stdClass;
use UnexpectedValueException;

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
            ->atLeast()
            ->once()
            ->andReturn('extension-a');

        $aActual = $metadata->get($aFile, MetadataTest__Value::class);

        self::assertEquals(new MetadataTest__Value($aResolver::class), $aActual);
        self::assertSame($aActual, $metadata->get($aFile, MetadataTest__Value::class));

        // Extension
        $bFile = Mockery::mock(File::class);
        $bFile
            ->shouldReceive('getExtension')
            ->atLeast()
            ->once()
            ->andReturn('extension-b');

        $bActual = $metadata->get($bFile, MetadataTest__Value::class);

        self::assertEquals(new MetadataTest__Value($bResolver::class), $bActual);
    }

    public function testGetCached(): void {
        // Prepare
        $resolved = new class() extends stdClass {
            // empty
        };
        $resolver = Mockery::mock(MetadataResolver::class);
        $resolver
            ->shouldReceive('getExtensions')
            ->once()
            ->andReturn(['*']);
        $resolver
            ->shouldReceive('isSupported')
            ->atLeast()
            ->once()
            ->andReturnUsing(
                static function (File $file, string $metadata): bool {
                    return is_a($metadata, stdClass::class, true);
                },
            );
        $resolver
            ->shouldReceive('resolve')
            ->once()
            ->andReturn($resolved);

        $container = Mockery::mock(ContainerResolver::class);
        $metadata  = new class($container) extends Metadata {
            #[Override]
            protected function addBuiltInResolvers(): void {
                // empty
            }
        };

        $metadata->addResolver($resolver);

        // Test
        $file = Mockery::mock(File::class);
        $file
            ->shouldReceive('getExtension')
            ->atLeast()
            ->once()
            ->andReturn('extension');

        $aActual = $metadata->get($file, $resolved::class);
        $bActual = $metadata->get($file, $resolved::class);
        $cActual = $metadata->get($file, stdClass::class);

        self::assertSame($resolved, $aActual);
        self::assertSame($resolved, $bActual);
        self::assertSame($resolved, $cActual);
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
            ->atLeast()
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
        } catch (MetadataUnresolvable $exception) {
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

        $file = Mockery::mock(File::class);
        $file
            ->shouldReceive('getExtension')
            ->atLeast()
            ->once()
            ->andReturn('extension');

        // Test
        $previous = null;

        try {
            $metadata->get($file, MetadataTest__Value::class);
        } catch (MetadataUnresolvable $exception) {
            $previous = $exception->getPrevious();
        }

        self::assertInstanceOf(RuntimeException::class, $previous);
        self::assertSame('Resolver not found.', $previous->getMessage());
    }

    public function testHas(): void {
        $file = Mockery::mock(File::class);
        $file
            ->shouldReceive('getExtension')
            ->atLeast()
            ->once()
            ->andReturn('extension');

        $value     = new class(__METHOD__) extends MetadataTest__Value {
            // empty
        };
        $container = Mockery::mock(ContainerResolver::class);
        $metadata  = new class($container) extends Metadata {
            #[Override]
            protected function addBuiltInResolvers(): void {
                // empty
            }
        };

        $metadata->addResolver(new MetadataTest__Resolver());

        self::assertFalse($metadata->has($file, MetadataTest__Value::class));
        self::assertFalse($metadata->has($file, $value::class));

        $metadata->set($file, $value);

        self::assertTrue($metadata->has($file, MetadataTest__Value::class));
        self::assertTrue($metadata->has($file, $value::class));
    }

    public function testSet(): void {
        $file = Mockery::mock(File::class);
        $file
            ->shouldReceive('getExtension')
            ->atLeast()
            ->once()
            ->andReturn('extension');

        $value     = new class(__METHOD__) extends MetadataTest__Value {
            // empty
        };
        $container = Mockery::mock(ContainerResolver::class);
        $metadata  = new class($container) extends Metadata {
            #[Override]
            protected function addBuiltInResolvers(): void {
                // empty
            }
        };

        $metadata->addResolver(new MetadataTest__Resolver());

        $metadata->set($file, $value);

        self::assertSame($value, $metadata->get($file, MetadataTest__Value::class));
        self::assertSame($value, $metadata->get($file, $value::class));
    }

    public function testReset(): void {
        $file = Mockery::mock(File::class);
        $file
            ->shouldReceive('getExtension')
            ->atLeast()
            ->once()
            ->andReturn('extension');

        $value     = new class(__METHOD__) extends MetadataTest__Value {
            // empty
        };
        $container = Mockery::mock(ContainerResolver::class);
        $metadata  = new class($container) extends Metadata {
            #[Override]
            protected function addBuiltInResolvers(): void {
                // empty
            }
        };

        $metadata->addResolver(new MetadataTest__Resolver());

        self::assertFalse($metadata->has($file, MetadataTest__Value::class));
        self::assertFalse($metadata->has($file, $value::class));

        $metadata->set($file, $value);

        self::assertTrue($metadata->has($file, MetadataTest__Value::class));
        self::assertTrue($metadata->has($file, $value::class));

        $metadata->reset($file);

        self::assertFalse($metadata->has($file, MetadataTest__Value::class));
        self::assertFalse($metadata->has($file, $value::class));
    }

    public function testSerialize(): void {
        // Prepare
        $file = Mockery::mock(File::class);
        $file
            ->shouldReceive('getExtension')
            ->atLeast()
            ->once()
            ->andReturn('extension');

        $value     = new MetadataTest__Value(__METHOD__);
        $resolver  = new class() extends MetadataTest__Resolver {
            #[Override]
            public function serialize(File $file, object $value): string {
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
        self::assertSame($value->value, $metadata->serialize($file, $value));
    }

    public function testSerializeNoResolver(): void {
        // Prepare
        $file = Mockery::mock(File::class);
        $file
            ->shouldReceive('getExtension')
            ->atLeast()
            ->once()
            ->andReturn('extension');

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
            $metadata->serialize($file, $value);
        } catch (MetadataUnserializable $exception) {
            $previous = $exception->getPrevious();
        }

        self::assertInstanceOf(RuntimeException::class, $previous);
        self::assertSame('Resolver not found.', $previous->getMessage());
    }

    public function testSerializeNoSerializer(): void {
        // Prepare
        $file = Mockery::mock(File::class);
        $file
            ->shouldReceive('getExtension')
            ->atLeast()
            ->once()
            ->andReturn('extension');

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
            $metadata->serialize($file, $value);
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
    public function isSupported(File $file, string $metadata): bool {
        return is_a($metadata, MetadataTest__Value::class, true);
    }

    #[Override]
    public function resolve(File $file, string $metadata): mixed {
        return new MetadataTest__Value($this::class);
    }

    #[Override]
    public function serialize(File $file, object $value): ?string {
        return null;
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
