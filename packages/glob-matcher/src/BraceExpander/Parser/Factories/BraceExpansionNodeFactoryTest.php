<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Parser\Factories;

use LastDragon_ru\GlobMatcher\BraceExpander\Ast\BraceExpansionNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\StringNode;
use LastDragon_ru\GlobMatcher\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(BraceExpansionNodeFactory::class)]
final class BraceExpansionNodeFactoryTest extends TestCase {
    public function testCreate(): void {
        $child   = new StringNode('node');
        $factory = new BraceExpansionNodeFactory();

        self::assertEquals(
            new BraceExpansionNode([]),
            $factory->create(),
        );

        $factory->push($child);

        self::assertEquals(
            new BraceExpansionNode([$child]),
            $factory->create(),
        );
    }
}
