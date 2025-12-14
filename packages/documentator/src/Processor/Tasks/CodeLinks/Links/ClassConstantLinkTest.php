<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Caster;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php\Parsed;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\Path\FilePath;
use Mockery;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\EnumCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_map;

/**
 * @internal
 */
#[CoversClass(ClassConstantLink::class)]
final class ClassConstantLinkTest extends TestCase {
    public function testToString(): void {
        self::assertSame('Class::Constant', (string) new ClassConstantLink('Class', 'Constant'));
        self::assertSame('App\\Class::Constant', (string) new ClassConstantLink('App\\Class', 'Constant'));
        self::assertSame(
            '\\App\\Class::Constant',
            (string) new ClassConstantLink('\\App\\Class', 'Constant'),
        );
    }

    public function testGetTitle(): void {
        self::assertSame('Class::Constant', (new ClassConstantLink('Class', 'Constant'))->getTitle());
        self::assertSame('Class::Constant', (new ClassConstantLink('App\\Class', 'Constant'))->getTitle());
        self::assertSame(
            'Class::Constant',
            (new ClassConstantLink('\\App\\Class', 'Constant'))->getTitle(),
        );
    }

    public function testGetTargetNodeClassConstant(): void {
        $filesystem = Mockery::mock(FileSystem::class);
        $caster     = Mockery::mock(Caster::class);
        $path       = new FilePath('/file.md');
        $file       = new File($filesystem, $caster, $path);

        $filesystem
            ->shouldReceive('read')
            ->with($file)
            ->once()
            ->andReturn(
                <<<'PHP'
                <?php declare(strict_types = 1);

                class A {
                    public const Constant = 123;
                }
                PHP,
            );

        $link = new class ('A', 'Constant') extends ClassConstantLink {
            #[Override]
            public function getTargetNode(ClassLike $class): ?Node {
                return parent::getTargetNode($class);
            }
        };

        $resolver = Mockery::mock(Resolver::class);
        $parsed   = ($this->app()->make(Parsed::class))($resolver, $file);
        $class    = array_first($parsed->classes);
        $actual   = $class !== null
            ? $link->getTargetNode($class->node)
            : null;

        self::assertInstanceOf(ClassConst::class, $actual);
        self::assertEquals(
            ['Constant'],
            array_map(
                static fn ($const) => (string) $const->name,
                $actual->consts,
            ),
        );
    }

    public function testGetTargetNodeEnum(): void {
        $filesystem = Mockery::mock(FileSystem::class);
        $caster     = Mockery::mock(Caster::class);
        $path       = new FilePath('/file.md');
        $file       = new File($filesystem, $caster, $path);

        $filesystem
            ->shouldReceive('read')
            ->with($file)
            ->once()
            ->andReturn(
                <<<'PHP'
                <?php declare(strict_types = 1);

                enum A {
                    case A;
                }
                PHP,
            );

        $link = new class ('A', 'A') extends ClassConstantLink {
            #[Override]
            public function getTargetNode(ClassLike $class): ?Node {
                return parent::getTargetNode($class);
            }
        };

        $resolver = Mockery::mock(Resolver::class);
        $parsed   = ($this->app()->make(Parsed::class))($resolver, $file);
        $class    = array_first($parsed->classes);
        $actual   = $class !== null
            ? $link->getTargetNode($class->node)
            : null;

        self::assertInstanceOf(EnumCase::class, $actual);
        self::assertSame('A', (string) $actual->name);
    }
}
