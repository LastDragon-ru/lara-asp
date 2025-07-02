<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Ast;

use LastDragon_ru\DiyParser\Testing\Package\TestCase;
use LogicException;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use Traversable;

use function array_map;
use function array_splice;
use function count;
use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(Cursor::class)]
final class CursorTest extends TestCase {
    public function testProperties(): void {
        $cursor = new Cursor(new CursorTest_LeafNode());

        self::assertNull($cursor->parent);
        self::assertNull($cursor->index);
        self::assertNull($cursor->previous);
        self::assertNull($cursor->next);
    }

    public function testPropertiesParent(): void {
        $a      = new CursorTest_LeafNode();
        $b      = new CursorTest_LeafNode();
        $c      = new CursorTest_LeafNode();
        $cursor = new Cursor(new CursorTest_ParentNode([$a, $b, $c]));
        $child  = $cursor[1];

        self::assertNotNull($child);
        self::assertSame($b, $child->node ?? null);
        self::assertSame(1, $child->index);
        self::assertSame($cursor->node, $child->parent->node ?? null);
        self::assertSame($a, $child->previous->node ?? null);
        self::assertSame($c, $child->next->node ?? null);
    }

    public function testCountLeaf(): void {
        self::assertCount(0, new Cursor(new CursorTest_LeafNode()));
    }

    public function testCountParent(): void {
        self::assertCount(1, new Cursor(new CursorTest_ParentNode([new CursorTest_LeafNode()])));
    }

    public function testGetIteratorLeaf(): void {
        $cursor = new Cursor(new CursorTest_LeafNode());
        $actual = iterator_to_array($cursor, false);

        self::assertSame([], $actual);
    }

    public function testGetIteratorParent(): void {
        $node   = new CursorTest_LeafNode();
        $cursor = new Cursor(new CursorTest_ParentNode([$node]));
        $actual = iterator_to_array($cursor, false);
        $actual = array_map(static fn ($c) => $c->node, $actual);

        self::assertSame([$node], $actual);
    }

    public function testOffsetExistsLeaf(): void {
        $cursor = new Cursor(new CursorTest_LeafNode());
        $actual = isset($cursor[0]);

        self::assertFalse($actual);
    }

    public function testOffsetExistsParent(): void {
        $child  = new CursorTest_LeafNode();
        $cursor = new Cursor(new CursorTest_ParentNode([$child]));
        $actual = isset($cursor[0]);

        self::assertTrue($actual);
    }

    public function testOffsetGetLeaf(): void {
        $cursor = new Cursor(new CursorTest_LeafNode());
        $actual = $cursor[0] ?? null;

        self::assertNull($actual);
    }

    public function testOffsetGetParent(): void {
        $child  = new CursorTest_LeafNode();
        $cursor = new Cursor(new CursorTest_ParentNode([$child]));
        $actual = $cursor[0]->node ?? null;

        self::assertSame($child, $actual);
    }

    public function testOffsetSet(): void {
        self::expectException(LogicException::class);

        $cursor   = new Cursor(new CursorTest_ParentNode([]));
        $cursor[] = new Cursor(new CursorTest_LeafNode());
    }

    public function testOffsetUnset(): void {
        self::expectException(LogicException::class);

        $cursor = new Cursor(new CursorTest_LeafNode());

        unset($cursor[0]);
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 */
class CursorTest_LeafNode {
    // empty
}

/**
 * @internal
 * @implements ParentNode<CursorTest_LeafNode>
 */
class CursorTest_ParentNode implements ParentNode {
    public function __construct(
        /**
         * @var list<CursorTest_LeafNode>
         */
        public array $children,
    ) {
        // empty
    }

    #[Override]
    public function count(): int {
        return count($this->children);
    }

    #[Override]
    public function getIterator(): Traversable {
        yield from $this->children;
    }

    #[Override]
    public function offsetExists(mixed $offset): bool {
        return isset($this->children[$offset]);
    }

    #[Override]
    public function offsetGet(mixed $offset): mixed {
        return $this->children[$offset];
    }

    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void {
        array_splice($this->children, $offset ?? count($this->children), 1, [$value]);
    }

    #[Override]
    public function offsetUnset(mixed $offset): void {
        array_splice($this->children, $offset, 1);
    }
}
