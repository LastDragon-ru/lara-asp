<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast;

use LastDragon_ru\GlobMatcher\Package\TestCase;
use LastDragon_ru\TextParser\Ast\Cursor;
use PHPUnit\Framework\Attributes\CoversClass;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(BraceExpansionNode::class)]
final class BraceExpansionNodeTest extends TestCase {
    public function testToIterable(): void {
        $node   = new BraceExpansionNode([
            new StringNode('path'),
            new SequenceNode([
                new BraceExpansionNode([new StringNode('/to')]),
                new BraceExpansionNode([new StringNode('/from')]),
                new BraceExpansionNode([]),
            ]),
            new StringNode('/file-'),
            new SequenceNode([
                new BraceExpansionNode([
                    new IntegerSequenceNode('00', '10'),
                    new StringNode('.'),
                    new SequenceNode([
                        new BraceExpansionNode([new StringNode('txt')]),
                        new BraceExpansionNode([new StringNode('js')]),
                    ]),
                ]),
                new BraceExpansionNode([
                    new CharacterSequenceNode('a', 'e'),
                    new StringNode('.php'),
                ]),
            ]),
            new StringNode('.tmp'),
        ]);
        $cursor = new Cursor($node);

        self::assertSame(
            [
                'path/to/file-00.txt.tmp',
                'path/to/file-00.js.tmp',
                'path/to/file-01.txt.tmp',
                'path/to/file-01.js.tmp',
                'path/to/file-02.txt.tmp',
                'path/to/file-02.js.tmp',
                'path/to/file-03.txt.tmp',
                'path/to/file-03.js.tmp',
                'path/to/file-04.txt.tmp',
                'path/to/file-04.js.tmp',
                'path/to/file-05.txt.tmp',
                'path/to/file-05.js.tmp',
                'path/to/file-06.txt.tmp',
                'path/to/file-06.js.tmp',
                'path/to/file-07.txt.tmp',
                'path/to/file-07.js.tmp',
                'path/to/file-08.txt.tmp',
                'path/to/file-08.js.tmp',
                'path/to/file-09.txt.tmp',
                'path/to/file-09.js.tmp',
                'path/to/file-10.txt.tmp',
                'path/to/file-10.js.tmp',
                'path/to/file-a.php.tmp',
                'path/to/file-b.php.tmp',
                'path/to/file-c.php.tmp',
                'path/to/file-d.php.tmp',
                'path/to/file-e.php.tmp',
                'path/from/file-00.txt.tmp',
                'path/from/file-00.js.tmp',
                'path/from/file-01.txt.tmp',
                'path/from/file-01.js.tmp',
                'path/from/file-02.txt.tmp',
                'path/from/file-02.js.tmp',
                'path/from/file-03.txt.tmp',
                'path/from/file-03.js.tmp',
                'path/from/file-04.txt.tmp',
                'path/from/file-04.js.tmp',
                'path/from/file-05.txt.tmp',
                'path/from/file-05.js.tmp',
                'path/from/file-06.txt.tmp',
                'path/from/file-06.js.tmp',
                'path/from/file-07.txt.tmp',
                'path/from/file-07.js.tmp',
                'path/from/file-08.txt.tmp',
                'path/from/file-08.js.tmp',
                'path/from/file-09.txt.tmp',
                'path/from/file-09.js.tmp',
                'path/from/file-10.txt.tmp',
                'path/from/file-10.js.tmp',
                'path/from/file-a.php.tmp',
                'path/from/file-b.php.tmp',
                'path/from/file-c.php.tmp',
                'path/from/file-d.php.tmp',
                'path/from/file-e.php.tmp',
                'path/file-00.txt.tmp',
                'path/file-00.js.tmp',
                'path/file-01.txt.tmp',
                'path/file-01.js.tmp',
                'path/file-02.txt.tmp',
                'path/file-02.js.tmp',
                'path/file-03.txt.tmp',
                'path/file-03.js.tmp',
                'path/file-04.txt.tmp',
                'path/file-04.js.tmp',
                'path/file-05.txt.tmp',
                'path/file-05.js.tmp',
                'path/file-06.txt.tmp',
                'path/file-06.js.tmp',
                'path/file-07.txt.tmp',
                'path/file-07.js.tmp',
                'path/file-08.txt.tmp',
                'path/file-08.js.tmp',
                'path/file-09.txt.tmp',
                'path/file-09.js.tmp',
                'path/file-10.txt.tmp',
                'path/file-10.js.tmp',
                'path/file-a.php.tmp',
                'path/file-b.php.tmp',
                'path/file-c.php.tmp',
                'path/file-d.php.tmp',
                'path/file-e.php.tmp',
            ],
            iterator_to_array($node::toIterable($cursor), false),
        );
    }
}
