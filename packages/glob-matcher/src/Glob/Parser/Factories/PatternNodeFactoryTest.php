<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Parser\Factories;

use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\PatternNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\Nodes\StringNode;
use LastDragon_ru\GlobMatcher\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(PatternNodeFactory::class)]
final class PatternNodeFactoryTest extends TestCase {
    public function testCreate(): void {
        $child   = new StringNode('node');
        $factory = new PatternNodeFactory();

        self::assertNull($factory->create());

        $factory->push($child);

        self::assertEquals(
            new PatternNode([$child]),
            $factory->create(),
        );
    }
}
