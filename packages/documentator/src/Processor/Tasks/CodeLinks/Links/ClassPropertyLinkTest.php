<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Caster;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php\ClassObject;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php\ClassObjectCast;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\Path\FilePath;
use Mockery;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_map;

/**
 * @internal
 */
#[CoversClass(ClassPropertyLink::class)]
final class ClassPropertyLinkTest extends TestCase {
    public function testToString(): void {
        self::assertSame('Class::$property', (string) new ClassPropertyLink('Class', 'property'));
        self::assertSame('App\\Class::$property', (string) new ClassPropertyLink('App\\Class', 'property'));
        self::assertSame(
            '\\App\\Class::$property',
            (string) new ClassPropertyLink('\\App\\Class', 'property'),
        );
    }

    public function testGetTitle(): void {
        self::assertSame('Class::$property', (new ClassPropertyLink('Class', 'property'))->getTitle());
        self::assertSame('Class::$property', (new ClassPropertyLink('App\\Class', 'property'))->getTitle());
        self::assertSame(
            'Class::$property',
            (new ClassPropertyLink('\\App\\Class', 'property'))->getTitle(),
        );
    }

    public function testGetTargetNode(): void {
        $filesystem = Mockery::mock(FileSystem::class);
        $caster     = Mockery::mock(Caster::class);
        $path       = new FilePath('/file.md');
        $file       = new File($filesystem, $path, $caster);

        $filesystem
            ->shouldReceive('read')
            ->with($file)
            ->once()
            ->andReturn(
                <<<'PHP'
                <?php declare(strict_types = 1);

                class A {
                    protected int $property = 123;
                }
                PHP,
            );

        $link = new class ('A', 'property') extends ClassPropertyLink {
            #[Override]
            public function getTargetNode(ClassLike $class): ?Node {
                return parent::getTargetNode($class);
            }
        };

        $resolver = $this->app()->make(ClassObjectCast::class);
        $class    = $resolver->castTo($file, ClassObject::class);
        $actual   = $class !== null
            ? $link->getTargetNode($class->class)
            : null;

        self::assertInstanceOf(Property::class, $actual);
        self::assertEquals(
            ['property'],
            array_map(
                static fn ($p) => $p->name->name,
                $actual->props,
            ),
        );
    }

    public function testGetTargetNodePromoted(): void {
        $filesystem = Mockery::mock(FileSystem::class);
        $caster     = Mockery::mock(Caster::class);
        $path       = new FilePath('/file.md');
        $file       = new File($filesystem, $path, $caster);

        $filesystem
            ->shouldReceive('read')
            ->with($file)
            ->once()
            ->andReturn(
                <<<'PHP'
                <?php declare(strict_types = 1);

                class A {
                    public function __construct(
                        protected int $property = 123,
                    ) {
                        // empty
                    }
                }
                PHP,
            );

        $link = new class ('A', 'property') extends ClassPropertyLink {
            #[Override]
            public function getTargetNode(ClassLike $class): ?Node {
                return parent::getTargetNode($class);
            }
        };

        $resolver = $this->app()->make(ClassObjectCast::class);
        $class    = $resolver->castTo($file, ClassObject::class);
        $actual   = $class !== null
            ? $link->getTargetNode($class->class)
            : null;

        self::assertInstanceOf(Param::class, $actual);
        self::assertInstanceOf(Variable::class, $actual->var);
        self::assertSame('property', $actual->var->name);
    }
}
