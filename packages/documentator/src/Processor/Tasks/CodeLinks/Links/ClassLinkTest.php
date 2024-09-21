<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

use LastDragon_ru\LaraASP\Documentator\Processor\Metadata\PhpClassComment;
use LastDragon_ru\LaraASP\Documentator\Testing\Package\TestCase;
use Mockery;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(ClassLink::class)]
final class ClassLinkTest extends TestCase {
    public function testToString(): void {
        $comment = Mockery::mock(PhpClassComment::class);

        self::assertEquals('Class', (string) new ClassLink($comment, 'Class'));
        self::assertEquals('App\\Class', (string) new ClassLink($comment, 'App\\Class'));
        self::assertEquals('\\App\\Class', (string) new ClassLink($comment, '\\App\\Class'));
    }

    public function testGetTitle(): void {
        $comment = Mockery::mock(PhpClassComment::class);

        self::assertEquals('Class', (new ClassLink($comment, 'Class'))->getTitle());
        self::assertEquals('Class', (new ClassLink($comment, 'App\\Class'))->getTitle());
        self::assertEquals('Class', (new ClassLink($comment, '\\App\\Class'))->getTitle());
    }

    public function testGetTargetNode(): void {
        $comment = Mockery::mock(PhpClassComment::class);
        $link    = new class ($comment, 'A') extends ClassLink {
            #[Override]
            public function getTargetNode(ClassLike $class): ?Node {
                return parent::getTargetNode($class);
            }
        };

        $class  = Mockery::mock(ClassLike::class);
        $actual = $link->getTargetNode($class);

        self::assertSame($class, $actual);
    }
}
