<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Parser\Factories;

use LastDragon_ru\GlobMatcher\BraceExpander\Ast\BraceExpansionNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\SequenceNode;
use LastDragon_ru\GlobMatcher\BraceExpander\Ast\StringNode;
use LastDragon_ru\GlobMatcher\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(SequenceNodeFactory::class)]
final class SequenceNodeFactoryTest extends TestCase {
    public function testCreate(): void {
        $aChild  = new BraceExpansionNode([]);
        $bChild  = new BraceExpansionNode([new StringNode('string')]);
        $factory = new SequenceNodeFactory();

        self::assertNull($factory->create());

        $factory->push($aChild);

        self::assertNull($factory->create());

        $factory->push($aChild);
        $factory->push($bChild);

        self::assertEquals(
            new SequenceNode([$aChild, $bChild]),
            $factory->create(),
        );
    }
}
