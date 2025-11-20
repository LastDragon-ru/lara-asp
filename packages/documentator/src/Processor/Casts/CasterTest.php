<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts;

use Illuminate\Contracts\Container\Container;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\FileSystem\Content;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Adapter;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Cast;
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
            public static function glob(): string {
                return '*.extension-b';
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

        $casts = new Casts($resolver);
        $casts->add($aCast, 100);
        $casts->add($bCast::class, 200);

        $adapter = Mockery::mock(Adapter::class);
        $caster  = new class($adapter, $casts) extends Caster {
            #[Override]
            protected function addBuiltInCasts(): void {
                // empty
            }
        };

        // Wildcard
        $aFile = Mockery::mock(File::class);
        $aFile
            ->shouldReceive('getName')
            ->atLeast()
            ->once()
            ->andReturn('file.extension-a');

        $aActual = $caster->castTo($aFile, CasterTest__Value::class);

        self::assertEquals(new CasterTest__Value($aCast::class), $aActual);
        self::assertSame($aActual, $caster->castTo($aFile, CasterTest__Value::class));

        // Extension
        $bFile = Mockery::mock(File::class);
        $bFile
            ->shouldReceive('getName')
            ->atLeast()
            ->once()
            ->andReturn('file.extension-b');

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
            ->shouldReceive('glob')
            ->once()
            ->andReturn('*');
        $cast
            ->shouldReceive('castTo')
            ->once()
            ->andReturn($value);

        $container = Mockery::mock(ContainerResolver::class);
        $casts     = new Casts($container);
        $casts->add($cast);

        $adapter = Mockery::mock(Adapter::class);
        $caster  = new class($adapter, $casts) extends Caster {
            #[Override]
            protected function addBuiltInCasts(): void {
                // empty
            }
        };

        // Test
        $file = Mockery::mock(File::class);
        $file
            ->shouldReceive('getName')
            ->atLeast()
            ->once()
            ->andReturn('file.extension');

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
            ->shouldReceive('getName')
            ->atLeast()
            ->once()
            ->andReturn('file.extension');

        $container = Mockery::mock(ContainerResolver::class);
        $casts     = new Casts($container);
        $casts->add($cast);

        $adapter = Mockery::mock(Adapter::class);
        $caster  = new class($adapter, $casts) extends Caster {
            #[Override]
            protected function addBuiltInCasts(): void {
                // empty
            }
        };

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
        $file  = Mockery::mock(File::class);
        $casts = Mockery::mock(Casts::class);
        $casts
            ->shouldReceive('get')
            ->once()
            ->with($file)
            ->andReturn([]);

        $adapter = Mockery::mock(Adapter::class);
        $caster  = new class($adapter, $casts) extends Caster {
            #[Override]
            protected function addBuiltInCasts(): void {
                // empty
            }
        };

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
            ->shouldReceive('getName')
            ->atLeast()
            ->once()
            ->andReturn('file.extension');

        $casts   = new Casts(Mockery::mock(ContainerResolver::class));
        $adapter = Mockery::mock(Adapter::class);
        $caster  = new class($adapter, $casts) extends Caster {
            #[Override]
            protected function addBuiltInCasts(): void {
                $this->casts->add(new CasterTest__Cast());
            }
        };

        // Test
        $value = $caster->castTo($file, CasterTest__Value::class);

        self::assertNotNull($caster->castFrom($file, new CasterTest__Value(__METHOD__)));
        self::assertNotSame($value, $caster->castTo($file, CasterTest__Value::class));
        self::assertSame(__METHOD__, $caster->castTo($file, Content::class)->content);
    }

    public function testCastFromImplementation(): void {
        // Prepare
        $file = Mockery::mock(File::class);
        $file
            ->shouldReceive('getName')
            ->atLeast()
            ->once()
            ->andReturn('file.md');

        $casts   = new Casts(Mockery::mock(ContainerResolver::class));
        $adapter = Mockery::mock(Adapter::class);
        $caster  = new class($adapter, $casts) extends Caster {
            #[Override]
            protected function addBuiltInCasts(): void {
                $this->casts->add(new CasterTest__InterfaceCast());
            }
        };

        // Test
        $value = $caster->castTo($file, CasterTest__Contract::class);

        self::assertSame(
            CasterTest__InterfaceCast::class,
            $caster->castFrom($file, $value),
        );
    }

    public function testCastFromContent(): void {
        // Prepare
        $file = Mockery::mock(File::class);
        $file
            ->shouldReceive('getName')
            ->atLeast()
            ->once()
            ->andReturn('file.extension');

        $casts   = new Casts(Mockery::mock(ContainerResolver::class));
        $adapter = Mockery::mock(Adapter::class);
        $caster  = new class($adapter, $casts) extends Caster {
            #[Override]
            protected function addBuiltInCasts(): void {
                $this->casts->add(new CasterTest__Cast());
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
            ->shouldReceive('getName')
            ->atLeast()
            ->once()
            ->andReturn('file.extension');

        $casts   = new Casts(Mockery::mock(ContainerResolver::class));
        $adapter = Mockery::mock(Adapter::class);
        $caster  = new class($adapter, $casts) extends Caster {
            #[Override]
            protected function addBuiltInCasts(): void {
                $this->casts->add(new CasterTest__Cast());
            }
        };

        // Test
        self::assertNull($caster->castFrom($file, $caster->castTo($file, CasterTest__Value::class)));
    }

    public function testCastFromSameContent(): void {
        // Prepare
        $file = Mockery::mock(File::class);
        $file
            ->shouldReceive('getName')
            ->atLeast()
            ->once()
            ->andReturn('file.extension');

        $casts   = new Casts(Mockery::mock(ContainerResolver::class));
        $adapter = Mockery::mock(Adapter::class);
        $caster  = new class($adapter, $casts) extends Caster {
            #[Override]
            protected function addBuiltInCasts(): void {
                $this->casts->add(new CasterTest__Cast());
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
        $file = Mockery::mock(File::class);
        $file
            ->shouldReceive('getName')
            ->once()
            ->andReturn('file.extension');

        $value   = new stdClass();
        $casts   = new Casts(Mockery::mock(ContainerResolver::class));
        $adapter = Mockery::mock(Adapter::class);
        $caster  = new class($adapter, $casts) extends Caster {
            #[Override]
            protected function addBuiltInCasts(): void {
                // empty
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
    public static function glob(): array|string {
        return '*';
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

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 * @implements Cast<CasterTest__Contract>
 */
class CasterTest__InterfaceCast implements Cast {
    #[Override]
    public static function getClass(): string {
        return CasterTest__Contract::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function glob(): string {
        return '*.md';
    }

    #[Override]
    public function castTo(File $file, string $class): ?object {
        return new class() implements CasterTest__Contract {
            // empty
        };
    }

    #[Override]
    public function castFrom(File $file, object $value): ?string {
        return $this::class;
    }
}

/**
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
interface CasterTest__Contract {
    // empty
}
