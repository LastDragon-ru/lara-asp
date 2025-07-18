<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Parser\Factories;

use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\PatternListNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\PatternListQuantifier;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\PatternNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\StringNode;
use LastDragon_ru\GlobMatcher\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(PatternListNodeFactory::class)]
final class PatternListNodeFactoryTest extends TestCase {
    public function testCreate(): void {
        $child   = new PatternNode([new StringNode('node')]);
        $factory = new PatternListNodeFactory(PatternListQuantifier::OneOf);

        self::assertEquals(
            new PatternListNode(PatternListQuantifier::OneOf, []),
            $factory->create(),
        );

        $factory->push($child);

        self::assertEquals(
            new PatternListNode(PatternListQuantifier::OneOf, [$child]),
            $factory->create(),
        );
    }
}
