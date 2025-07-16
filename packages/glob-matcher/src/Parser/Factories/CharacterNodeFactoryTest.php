<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Parser\Factories;

use LastDragon_ru\GlobMatcher\Ast\Nodes\CharacterNode;
use LastDragon_ru\GlobMatcher\Ast\Nodes\StringNode;
use LastDragon_ru\GlobMatcher\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(CharacterNodeFactory::class)]
final class CharacterNodeFactoryTest extends TestCase {
    public function testCreate(): void {
        $child   = new StringNode('node');
        $factory = new CharacterNodeFactory(true);

        self::assertNull($factory->create());

        $factory->push($child);

        self::assertEquals(
            new CharacterNode(true, [$child]),
            $factory->create(),
        );
    }
}
