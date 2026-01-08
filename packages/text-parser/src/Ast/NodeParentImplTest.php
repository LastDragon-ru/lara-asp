<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Ast;

use LastDragon_ru\TextParser\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(NodeParentImpl::class)]
final class NodeParentImplTest extends TestCase {
    public function testCountable(): void {
        $child  = new ParentNodeTest_ChildNode();
        $parent = new ParentNodeTest_ParentNode([$child]);

        self::assertCount(1, $parent);
    }

    public function testArrayAccess(): void {
        $aChild = new ParentNodeTest_ChildNode();
        $bChild = new ParentNodeTest_ChildNode();
        $cChild = new ParentNodeTest_ChildNode();
        $parent = new ParentNodeTest_ParentNode([$aChild]);

        self::assertSame($aChild, $parent[0]);
        self::assertNull($parent[1]);

        $parent[]  = $bChild;
        $parent[2] = $cChild;

        self::assertSame($bChild, $parent[1]);
        self::assertSame($cChild, $parent[2]);

        $parent[1] = $cChild;

        self::assertSame($cChild, $parent[1]);

        unset($parent[0]);

        self::assertEquals(
            [$cChild, $cChild],
            iterator_to_array($parent, false),
        );
    }

    public function testIteratorAggregate(): void {
        $aChild = new ParentNodeTest_ChildNode();
        $bChild = new ParentNodeTest_ChildNode();
        $parent = new ParentNodeTest_ParentNode([$aChild, $bChild]);

        self::assertEquals([$aChild, $bChild], iterator_to_array($parent, false));
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @implements NodeChild<ParentNodeTest_ParentNode>
 */
class ParentNodeTest_ChildNode implements NodeChild {
    // empty
}

/**
 * @internal
 * @extends NodeParentImpl<ParentNodeTest_ChildNode>
 */
class ParentNodeTest_ParentNode extends NodeParentImpl {
    // empty
}
