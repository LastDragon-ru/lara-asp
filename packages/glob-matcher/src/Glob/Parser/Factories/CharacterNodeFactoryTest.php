<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Parser\Factories;

use LastDragon_ru\GlobMatcher\Glob\Ast\CharacterNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\StringNode;
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
