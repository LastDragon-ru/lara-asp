<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Parser\Factories;

use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\GlobNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\SegmentNode;
use LastDragon_ru\GlobMatcher\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(GlobNodeFactory::class)]
final class GlobNodeFactoryTest extends TestCase {
    public function testCreate(): void {
        $child   = new SegmentNode();
        $factory = new GlobNodeFactory();

        self::assertNull($factory->create());

        $factory->push($child);

        self::assertEquals(
            new GlobNode([$child]),
            $factory->create(),
        );
    }
}
