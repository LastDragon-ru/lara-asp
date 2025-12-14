<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\LaraASP\Documentator\Processor\Casts\Php\Parsed;
use LastDragon_ru\LaraASP\Documentator\Processor\Contracts\Resolver;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\FileSystem;
use LastDragon_ru\Path\FilePath;
use Mockery;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_first;

/**
 * @internal
 */
#[CoversClass(ClassMethodLink::class)]
final class ClassMethodLinkTest extends TestCase {
    public function testToString(): void {
        self::assertSame('Class::method()', (string) new ClassMethodLink('Class', 'method'));
        self::assertSame('App\\Class::method()', (string) new ClassMethodLink('App\\Class', 'method'));
        self::assertSame('\\App\\Class::method()', (string) new ClassMethodLink('\\App\\Class', 'method'));
    }

    public function testGetTitle(): void {
        self::assertSame('Class::method()', (new ClassMethodLink('Class', 'method'))->getTitle());
        self::assertSame('Class::method()', (new ClassMethodLink('App\\Class', 'method'))->getTitle());
        self::assertSame('Class::method()', (new ClassMethodLink('\\App\\Class', 'method'))->getTitle());
    }

    public function testGetTargetNode(): void {
        $filesystem = Mockery::mock(FileSystem::class);
        $path       = new FilePath('/file.md');
        $file       = new File($filesystem, $path);

        $filesystem
            ->shouldReceive('read')
            ->with($file)
            ->once()
            ->andReturn(
                <<<'PHP'
                <?php declare(strict_types = 1);

                class A {
                    protected function method(): void {
                        // empty
                    }
                }
                PHP,
            );
        $link = new class ('A', 'method') extends ClassMethodLink {
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

        self::assertInstanceOf(ClassMethod::class, $actual);
        self::assertSame('method', $actual->name->name);
    }
}
