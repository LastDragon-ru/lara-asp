<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Parser\Factories;

use LastDragon_ru\GlobMatcher\Ast\Nodes\NameNode;
use LastDragon_ru\GlobMatcher\Ast\Nodes\StringNode;
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

        $factory->push($child);

        self::assertEquals(
            new NameNode([$child]),
            $factory->create(),
        );
    }
}
