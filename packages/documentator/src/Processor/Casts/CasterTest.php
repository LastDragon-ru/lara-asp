<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts;

use Illuminate\Contracts\Container\Container;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\FileSystem\Content;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Cast;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\FileSystemAdapter;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\CastFromFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\Exceptions\CastToFailed;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use RuntimeException;
use stdClass;
use UnexpectedValueException;

use function sprintf;

/**
 * @internal
 */
#[CoversClass(Caster::class)]
final class CasterTest extends TestCase {
    public function testCastTo(): void {
        // Prepare
        $aCast     = new CasterTest__Cast();
        $bCast     = new class() extends CasterTest__Cast {
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
            ->with($bCast::class)
            ->once()
            ->andReturn($bCast);

        $adapter = Mockery::mock(FileSystemAdapter::class);
        $caster  = new class($resolver, $adapter) extends Caster {
            #[Override]
            protected function addBuiltInCasts(): void {
                // empty
            }
        };

        $caster->addCast($aCast, 100);
        $caster->addCast($bCast::class, 200);

        // Wildcard
        $aFile = Mockery::mock(File::class);
        $aFile
            ->shouldReceive('getExtension')
            ->atLeast()
            ->once()
            ->andReturn('extension-a');

        $aActual = $caster->castTo($aFile, CasterTest__Value::class);

        self::assertEquals(new CasterTest__Value($aCast::class), $aActual);
        self::assertSame($aActual, $caster->castTo($aFile, CasterTest__Value::class));

        // Extension
        $bFile = Mockery::mock(File::class);
        $bFile
            ->shouldReceive('getExtension')
            ->atLeast()
            ->once()
            ->andReturn('extension-b');

        $bActual = $caster->castTo($bFile, CasterTest__Value::class);

        self::assertEquals(new CasterTest__Value($bCast::class), $bActual);
    }

    public function testCastToCached(): void {
        // Prepare
        $value = new stdClass();
        $cast  = Mockery::mock(Cast::class);
        $cast
            ->shouldReceive('getClass')
            ->once()
            ->andReturn(stdClass::class);
        $cast
            ->shouldReceive('getExtensions')
            ->once()
            ->andReturn(['*']);
        $cast
            ->shouldReceive('castTo')
            ->once()
            ->andReturn($value);

        $container = Mockery::mock(ContainerResolver::class);
        $adapter   = Mockery::mock(FileSystemAdapter::class);
        $caster    = new class($container, $adapter) extends Caster {
            #[Override]
            protected function addBuiltInCasts(): void {
                // empty
            }
        };

        $caster->addCast($cast);

        // Test
        $file = Mockery::mock(File::class);
        $file
            ->shouldReceive('getExtension')
            ->atLeast()
            ->once()
            ->andReturn('extension');

        $aActual = $caster->castTo($file, $value::class);
        $bActual = $caster->castTo($file, $value::class);
        $cActual = $caster->castTo($file, stdClass::class);

        self::assertSame($value, $aActual);
        self::assertSame($value, $bActual);
        self::assertSame($value, $cActual);
    }

    public function testCastToUnexpected(): void {
        // Prepare
        $cast = Mockery::mock(CasterTest__Cast::class);
        $cast->makePartial();
        $cast
            ->shouldReceive('castTo')
            ->once()
            ->andReturn(new stdClass());

        $file = Mockery::mock(File::class);
        $file
            ->shouldReceive('getExtension')
            ->atLeast()
            ->once()
            ->andReturn('extension');

        $container = Mockery::mock(ContainerResolver::class);
        $adapter   = Mockery::mock(FileSystemAdapter::class);
        $caster    = new class($container, $adapter) extends Caster {
            #[Override]
            protected function addBuiltInCasts(): void {
                // empty
            }
        };

        $caster->addCast($cast);

        // Test
        $previous = null;

        try {
            $caster->castTo($file, CasterTest__Value::class);
        } catch (CastToFailed $exception) {
            $previous = $exception->getPrevious();
        }

        self::assertInstanceOf(UnexpectedValueException::class, $previous);
        self::assertSame(
            sprintf(
                'Expected `%s`, got `%s` (cast `%s`).',
                CasterTest__Value::class,
                stdClass::class,
                $cast::class,
            ),
            $previous->getMessage(),
        );
    }

    public function testCastToNoCast(): void {
        // Prepare
        $container = Mockery::mock(ContainerResolver::class);
        $adapter   = Mockery::mock(FileSystemAdapter::class);
        $caster    = new class($container, $adapter) extends Caster {
            #[Override]
            protected function addBuiltInCasts(): void {
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
            $caster->castTo($file, CasterTest__Value::class);
        } catch (CastToFailed $exception) {
            $previous = $exception->getPrevious();
        }

        self::assertInstanceOf(RuntimeException::class, $previous);
        self::assertSame('Cast not found.', $previous->getMessage());
    }

    public function testCastFromObject(): void {
        // Prepare
        $file = Mockery::mock(File::class);
        $file
            ->shouldReceive('getExtension')
            ->atLeast()
            ->once()
            ->andReturn('extension');

        $container = Mockery::mock(ContainerResolver::class);
        $adapter   = Mockery::mock(FileSystemAdapter::class);
        $caster    = new class($container, $adapter) extends Caster {
            #[Override]
            protected function addBuiltInCasts(): void {
                $this->addCast(new CasterTest__Cast());
            }
        };

        // Test
        $value = $caster->castTo($file, CasterTest__Value::class);

        self::assertNotNull($caster->castFrom($file, new CasterTest__Value(__METHOD__)));
        self::assertNotSame($value, $caster->castTo($file, CasterTest__Value::class));
        self::assertSame(__METHOD__, $caster->castTo($file, Content::class)->content);
    }

    public function testCastFromContent(): void {
        // Prepare
        $file = Mockery::mock(File::class);
        $file
            ->shouldReceive('getExtension')
            ->atLeast()
            ->once()
            ->andReturn('extension');

        $container = Mockery::mock(ContainerResolver::class);
        $adapter   = Mockery::mock(FileSystemAdapter::class);
        $caster    = new class($container, $adapter) extends Caster {
            #[Override]
            protected function addBuiltInCasts(): void {
                $this->addCast(new CasterTest__Cast());
            }
        };

        // Test
        $value = $caster->castTo($file, CasterTest__Value::class);

        self::assertNotNull($caster->castFrom($file, new Content(CasterTest__Value::class)));
        self::assertNotSame($value, $caster->castTo($file, CasterTest__Value::class));
    }

    public function testCastFromSameValue(): void {
        // Prepare
        $file = Mockery::mock(File::class);
        $file
            ->shouldReceive('getExtension')
            ->atLeast()
            ->once()
            ->andReturn('extension');

        $container = Mockery::mock(ContainerResolver::class);
        $adapter   = Mockery::mock(FileSystemAdapter::class);
        $caster    = new class($container, $adapter) extends Caster {
            #[Override]
            protected function addBuiltInCasts(): void {
                $this->addCast(new CasterTest__Cast());
            }
        };

        // Test
        self::assertNull($caster->castFrom($file, $caster->castTo($file, CasterTest__Value::class)));
    }

    public function testCastFromSameContent(): void {
        // Prepare
        $file = Mockery::mock(File::class);
        $file
            ->shouldReceive('getExtension')
            ->atLeast()
            ->once()
            ->andReturn('extension');

        $container = Mockery::mock(ContainerResolver::class);
        $adapter   = Mockery::mock(FileSystemAdapter::class);
        $caster    = new class($container, $adapter) extends Caster {
            #[Override]
            protected function addBuiltInCasts(): void {
                $this->addCast(new CasterTest__Cast());
            }
        };

        self::assertNotNull($caster->castFrom($file, new Content(CasterTest__Cast::class)));

        // Test
        $value = $caster->castTo($file, CasterTest__Value::class);

        self::assertNull($caster->castFrom($file, new CasterTest__Value($value->value)));
        self::assertSame($value, $caster->castTo($file, CasterTest__Value::class));
    }

    public function testCastFromNoCast(): void {
        // Prepare
        $file      = Mockery::mock(File::class);
        $value     = new stdClass();
        $container = Mockery::mock(ContainerResolver::class);
        $adapter   = Mockery::mock(FileSystemAdapter::class);
        $caster    = new class($container, $adapter) extends Caster {
            #[Override]
            protected function addBuiltInCasts(): void {
                // empty
            }

            #[Override]
            protected function getCast(File $file, string $class): Cast {
                throw new RuntimeException('Cast not found.');
            }
        };

        // Test
        $previous = null;

        try {
            $caster->castFrom($file, $value);
        } catch (CastFromFailed $exception) {
            $previous = $exception->getPrevious();
        }

        self::assertInstanceOf(RuntimeException::class, $previous);
        self::assertSame('Cast not found.', $previous->getMessage());
    }

    public function testGetTags(): void {
        $file = Mockery::mock(File::class);
        $file
            ->shouldReceive('getExtension')
            ->atLeast()
            ->once()
            ->andReturn('extension');

        $container = Mockery::mock(ContainerResolver::class);
        $adapter   = Mockery::mock(FileSystemAdapter::class);
        $caster    = new class($container, $adapter) extends Caster {
            #[Override]
            protected function addBuiltInCasts(): void {
                // empty
            }

            /**
             * @inheritDoc
             */
            #[Override]
            public function getTags(File $file, string $class): array {
                return parent::getTags($file, $class);
            }
        };

        self::assertSame(
            [
                'stdClass:extension',
                'stdClass:*',
            ],
            $caster->getTags($file, stdClass::class),
        );
        self::assertSame(
            [
                Cast::class.':extension',
                Cast::class.':*',
            ],
            $caster->getTags($file, Cast::class),
        );
        self::assertSame(
            [
                CasterTest__Cast::class.':extension',
                CasterTest__Cast::class.':*',
                Cast::class.':extension',
                Cast::class.':*',
            ],
            $caster->getTags($file, CasterTest__Cast::class),
        );
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 * @implements Cast<CasterTest__Value>
 */
class CasterTest__Cast implements Cast {
    #[Override]
    public static function getClass(): string {
        return CasterTest__Value::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function getExtensions(): array {
        return ['*'];
    }

    #[Override]
    public function castTo(File $file, string $class): ?object {
        return new CasterTest__Value($this::class);
    }

    #[Override]
    public function castFrom(File $file, object $value): ?string {
        return $value->value;
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class CasterTest__Value {
    public function __construct(
        public readonly string $value,
    ) {
        // empty
    }
}
