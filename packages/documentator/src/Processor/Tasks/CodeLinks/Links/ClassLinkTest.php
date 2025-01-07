<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Links;

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
        self::assertSame('Class', (string) new ClassLink('Class'));
        self::assertSame('App\\Class', (string) new ClassLink('App\\Class'));
        self::assertSame('\\App\\Class', (string) new ClassLink('\\App\\Class'));
    }

    public function testGetTitle(): void {
        self::assertSame('Class', (new ClassLink('Class'))->getTitle());
        self::assertSame('Class', (new ClassLink('App\\Class'))->getTitle());
        self::assertSame('Class', (new ClassLink('\\App\\Class'))->getTitle());
    }

    public function testGetTargetNode(): void {
        $link = new class ('A') extends ClassLink {
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
