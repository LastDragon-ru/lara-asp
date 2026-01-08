<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Ast;

use LastDragon_ru\TextParser\Package\TestCase;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(NodeParentFactory::class)]
final class NodeParentFactoryTest extends TestCase {
    public function testPush(): void {
        $factory = new NodeParentFactoryTest_Factory();
        $childA  = new NodeParentFactoryTest_Child();
        $childB  = new NodeParentFactoryTest_ChildIgnored();
        $childC  = new class() extends NodeParentFactoryTest_Child {
            // empty
        };

        self::assertFalse($factory->push(null));
        self::assertTrue($factory->push($childA));
        self::assertTrue($factory->push($childA));
        self::assertFalse($factory->push(null));
        self::assertFalse($factory->push($childB));
        self::assertFalse($factory->push(null));
        self::assertTrue($factory->push($childC));
        self::assertTrue($factory->push($childC));

        self::assertEquals(
            [
                $childA,
                $childC,
            ],
            $factory->create()?->children,
        );
    }

    public function testIsEmpty(): void {
        $factory = new NodeParentFactoryTest_Factory();

        self::assertTrue($factory->isEmpty());

        $factory->push(null);

        self::assertTrue($factory->isEmpty());

        $factory->push(new NodeParentFactoryTest_Child());

        self::assertFalse($factory->isEmpty());
    }

    public function testCreate(): void {
        $child   = new NodeParentFactoryTest_Child();
        $parent  = new NodeParentFactoryTest_Parent([$child]);
        $factory = Mockery::mock(NodeParentFactoryTest_Factory::class);
        $factory->shouldAllowMockingProtectedMethods();
        $factory->makePartial();
        $factory
            ->shouldReceive('onCreate')
            ->with([$child])
            ->once()
            ->andReturn($parent);

        $factory->push($child);

        self::assertEquals($parent, $factory->create());
        self::assertTrue($factory->isEmpty());
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

/**
 * @internal
 * @extends NodeParentImpl<NodeParentFactoryTest_Child>
 */
class NodeParentFactoryTest_Parent extends NodeParentImpl {
    // empty
}

/**
 * @internal
 * @implements NodeChild<NodeParentFactoryTest_Parent>
 */
class NodeParentFactoryTest_Child implements NodeChild, NodeMergeable {
    #[Override]
    public static function merge(NodeMergeable $previous, NodeMergeable $current): NodeMergeable {
        if ($previous::class === $current::class) {
            $current = $previous;
        }

        return $current;
    }
}

/**
 * @internal
 */
class NodeParentFactoryTest_ChildIgnored extends NodeParentFactoryTest_Child {
    // empty
}

/**
 * @internal
 * @extends NodeParentFactory<NodeParentFactoryTest_Parent, NodeParentFactoryTest_Child>
 */
class NodeParentFactoryTest_Factory extends NodeParentFactory {
    /**
     * @inheritDoc
     */
    #[Override]
    protected function onCreate(array $children): ?object {
        return new NodeParentFactoryTest_Parent($children);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function onPush(array $children, ?object $node): bool {
        return !($node instanceof NodeParentFactoryTest_ChildIgnored);
    }
}
