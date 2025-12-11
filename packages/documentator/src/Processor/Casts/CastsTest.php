<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Casts;

use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Cast;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File as FileImpl;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\Path\FilePath;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(Casts::class)]
final class CastsTest extends TestCase {
    public function testGet(): void {
        $casts = new Casts(Mockery::mock(ContainerResolver::class));
        $aCast = new CastsTest__Cast();
        $bCast = new class() extends CastsTest__Cast {
            #[Override]
            public static function glob(): string {
                return '*.md';
            }
        };
        $cCast = new class() extends CastsTest__Cast {
            // empty
        };

        $casts->add($aCast);
        $casts->add($bCast, 200);
        $casts->add($cCast, 100);

        $filesystem = Mockery::mock(FileSystem::class);
        $caster     = Mockery::mock(Caster::class);
        $aFile      = Mockery::mock(FileImpl::class, [$filesystem, $caster, new FilePath('/file.md')]);
        $bFile      = Mockery::mock(FileImpl::class, [$filesystem, $caster, new FilePath('/file.txt')]);

        self::assertSame([$bCast, $cCast, $aCast], iterator_to_array($casts->get($aFile), false));
        self::assertSame([$cCast, $aCast], iterator_to_array($casts->get($bFile), false));
    }

    public function testAdd(): void {
        $casts = new Casts(Mockery::mock(ContainerResolver::class));

        $casts->add(CastsTest__Cast::class);

        self::assertSame(
            [CastsTest__Cast::class],
            iterator_to_array($casts, false),
        );
    }

    public function testRemove(): void {
        $casts = new Casts(Mockery::mock(ContainerResolver::class));
        $cast  = new class() extends CastsTest__Cast {
            // empty
        };

        $casts->add($cast);
        $casts->add(CastsTest__Cast::class);

        self::assertSame(
            [
                CastsTest__Cast::class,
                $cast::class,
            ],
            iterator_to_array($casts, false),
        );

        $casts->remove($cast);
        $casts->remove(CastsTest__Cast::class);

        self::assertSame([], iterator_to_array($casts, false));
    }

    public function testGetIterator(): void {
        $casts = new Casts(Mockery::mock(ContainerResolver::class));
        $cast  = new class() extends CastsTest__Cast {
            // empty
        };

        $casts->add($cast, 100);
        $casts->add(CastsTest__Cast::class, 200);

        self::assertSame(
            [
                CastsTest__Cast::class,
                $cast::class,
            ],
            iterator_to_array($casts, false),
        );
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @implements Cast<stdClass>
 *
 * @internal
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */
class CastsTest__Cast implements Cast {
    /**
     * @inheritDoc
     */
    #[Override]
    public static function class(): string {
        return stdClass::class;
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
        return new stdClass();
    }

    #[Override]
    public function castFrom(File $file, object $value): ?string {
        return $value::class;
    }
}
