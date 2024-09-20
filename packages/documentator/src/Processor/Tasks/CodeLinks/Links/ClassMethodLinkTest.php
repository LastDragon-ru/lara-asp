<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\PhpClass;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\PhpClassComment;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(ClassMethodLink::class)]
final class ClassMethodLinkTest extends TestCase {
    public function testToString(): void {
        $comment = Mockery::mock(PhpClassComment::class);

        self::assertEquals('Class::method()', (string) new ClassMethodLink($comment, 'Class', 'method'));
        self::assertEquals('App\\Class::method()', (string) new ClassMethodLink($comment, 'App\\Class', 'method'));
        self::assertEquals('\\App\\Class::method()', (string) new ClassMethodLink($comment, '\\App\\Class', 'method'));
    }

    public function testGetTitle(): void {
        $comment = Mockery::mock(PhpClassComment::class);

        self::assertEquals('Class::method()', (new ClassMethodLink($comment, 'Class', 'method'))->getTitle());
        self::assertEquals('Class::method()', (new ClassMethodLink($comment, 'App\\Class', 'method'))->getTitle());
        self::assertEquals('Class::method()', (new ClassMethodLink($comment, '\\App\\Class', 'method'))->getTitle());
    }

    public function testGetTargetNode(): void {
        $file = Mockery::mock(File::class);
        $file->makePartial();
        $file
            ->shouldReceive('getContent')
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

        $comment = Mockery::mock(PhpClassComment::class);
        $link    = new class ($comment, 'A', 'method') extends ClassMethodLink {
            #[Override]
            public function getTargetNode(ClassLike $class): ?Node {
                return parent::getTargetNode($class);
            }
        };

        $class = $file->getMetadata($this->app()->make(PhpClass::class));

        self::assertNotNull($class);

        $actual = $link->getTargetNode($class->class);

        self::assertInstanceOf(ClassMethod::class, $actual);
        self::assertEquals('method', $actual->name->name);
    }
}
