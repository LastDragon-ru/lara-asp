<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast;

use LastDragon_ru\GlobMatcher\Package\TestCase;
use LastDragon_ru\TextParser\Ast\Cursor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(IntegerSequenceNode::class)]
final class IntegerSequenceNodeTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param list<string> $expected
     */
    #[DataProvider('dataProviderToIterable')]
    public function testToIterable(array $expected, IntegerSequenceNode $node): void {
        self::assertSame($expected, iterator_to_array($node::toIterable(new Cursor($node)), false));
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<string, array{list<string>, IntegerSequenceNode}>
     */
    public static function dataProviderToIterable(): array {
        return [
            '-> without increment'                                => [
                ['1', '2', '3', '4', '5', '6', '7', '8', '9'],
                new IntegerSequenceNode('1', '9'),
            ],
            '-> with positive increment'                          => [
                ['1', '4', '7'],
                new IntegerSequenceNode('1', '9', 3),
            ],
            '-> with negative increment'                          => [
                ['1', '4', '7'],
                new IntegerSequenceNode('1', '9', -3),
            ],
            '<- without increment'                                => [
                ['9', '8', '7', '6', '5', '4', '3', '2', '1'],
                new IntegerSequenceNode('9', '1'),
            ],
            '<- with positive increment'                          => [
                ['9', '6', '3'],
                new IntegerSequenceNode('9', '1', 3),
            ],
            '<- with negative increment'                          => [
                ['9', '6', '3'],
                new IntegerSequenceNode('9', '1', -3),
            ],
            '-> max padding should be used'                       => [
                ['001', '003', '005', '007', '009'],
                new IntegerSequenceNode('01', '009', 2),
            ],
            '-> padding should be equal to max length'            => [
                ['001', '021', '041', '061', '081', '101', '121', '141', '161', '181'],
                new IntegerSequenceNode('01', '200', 20),
            ],
            '-> (negative) max padding should be used'            => [
                ['-001', '-003', '-005', '-007', '-009'],
                new IntegerSequenceNode('-01', '-009', 2),
            ],
            '-> (negative) padding should be equal to max length' => [
                ['-001', '-021', '-041', '-061', '-081', '-101', '-121', '-141', '-161', '-181'],
                new IntegerSequenceNode('-01', '-200', 20),
            ],
        ];
    }
    // </editor-fold>
}
