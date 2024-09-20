<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\PhpClass;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\PhpClassComment;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PHPUnit\Framework\Attributes\CoversClass;

use function array_map;

/**
 * @internal
 */
#[CoversClass(ClassConstantLink::class)]
final class ClassConstantLinkTest extends TestCase {
    public function testToString(): void {
        $comment = Mockery::mock(PhpClassComment::class);

        self::assertEquals('Class::Constant', (string) new ClassConstantLink($comment, 'Class', 'Constant'));
        self::assertEquals('App\\Class::Constant', (string) new ClassConstantLink($comment, 'App\\Class', 'Constant'));
        self::assertEquals('\\App\\Class::Constant', (string) new ClassConstantLink($comment, '\\App\\Class', 'Constant'));
    }

    public function testGetTitle(): void {
        $comment = Mockery::mock(PhpClassComment::class);

        self::assertEquals('Class::Constant', (new ClassConstantLink($comment, 'Class', 'Constant'))->getTitle());
        self::assertEquals('Class::Constant', (new ClassConstantLink($comment, 'App\\Class', 'Constant'))->getTitle());
        self::assertEquals('Class::Constant', (new ClassConstantLink($comment, '\\App\\Class', 'Constant'))->getTitle());
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
                    public const Constant = 123;
                }
                PHP,
            );

        $comment = Mockery::mock(PhpClassComment::class);
        $link    = new class ($comment, 'A', 'Constant') extends ClassConstantLink {
            #[Override]
            public function getTargetNode(ClassLike $class): ?Node {
                return parent::getTargetNode($class);
            }
        };

        $class = $file->getMetadata($this->app()->make(PhpClass::class));

        self::assertNotNull($class);

        $actual = $link->getTargetNode($class->class);

        self::assertInstanceOf(ClassConst::class, $actual);
        self::assertEquals(
            ['Constant'],
            array_map(
                static fn ($const) => (string) $const->name,
                $actual->consts,
            ),
        );
    }
}
