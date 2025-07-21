<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Parser\Factories;

use LastDragon_ru\GlobMatcher\Glob\Ast\NameNode;
use LastDragon_ru\GlobMatcher\Glob\Ast\StringNode;
use LastDragon_ru\GlobMatcher\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(NameNodeFactory::class)]
final class NameNodeFactoryTest extends TestCase {
    public function testCreate(): void {
        $child   = new StringNode('node');
        $factory = new NameNodeFactory();

        self::assertNull($factory->create());

        $factory->push($child);

        self::assertEquals(
            new NameNode([$child]),
            $factory->create(),
        );
    }
}
