<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Processor\FileSystem\File;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\PhpClass;
use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\PhpClassComment;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
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
        $comment = Mockery::mock(PhpClassComment::class);

        self::assertEquals('Class::$property', (string) new ClassPropertyLink($comment, 'Class', 'property'));
        self::assertEquals('App\\Class::$property', (string) new ClassPropertyLink($comment, 'App\\Class', 'property'));
        self::assertEquals(
            '\\App\\Class::$property',
            (string) new ClassPropertyLink($comment, '\\App\\Class', 'property'),
        );
    }

    public function testGetTitle(): void {
        $comment = Mockery::mock(PhpClassComment::class);

        self::assertEquals('Class::$property', (new ClassPropertyLink($comment, 'Class', 'property'))->getTitle());
        self::assertEquals('Class::$property', (new ClassPropertyLink($comment, 'App\\Class', 'property'))->getTitle());
        self::assertEquals(
            'Class::$property',
            (new ClassPropertyLink($comment, '\\App\\Class', 'property'))->getTitle(),
        );
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
                    protected int $property = 123;
                }
                PHP,
            );

        $comment = Mockery::mock(PhpClassComment::class);
        $link    = new class ($comment, 'A', 'property') extends ClassPropertyLink {
            #[Override]
            public function getTargetNode(ClassLike $class): ?Node {
                return parent::getTargetNode($class);
            }
        };

        $class = $file->getMetadata($this->app()->make(PhpClass::class));

        self::assertNotNull($class);

        $actual = $link->getTargetNode($class->class);

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
        $file = Mockery::mock(File::class);
        $file->makePartial();
        $file
            ->shouldReceive('getContent')
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

        $comment = Mockery::mock(PhpClassComment::class);
        $link    = new class ($comment, 'A', 'property') extends ClassPropertyLink {
            #[Override]
            public function getTargetNode(ClassLike $class): ?Node {
                return parent::getTargetNode($class);
            }
        };

        $class = $file->getMetadata($this->app()->make(PhpClass::class));

        self::assertNotNull($class);

        $actual = $link->getTargetNode($class->class);

        self::assertInstanceOf(Param::class, $actual);
        self::assertInstanceOf(Variable::class, $actual->var);
        self::assertEquals('property', $actual->var->name);
    }
}
