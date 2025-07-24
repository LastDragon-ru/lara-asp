<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander;

use LastDragon_ru\GlobMatcher\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(BraceExpander::class)]
final class BraceExpanderTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param list<string> $expected
     */
    #[DataProvider('dataProviderGetIterable')]
    public function testGetIterator(array $expected, string $pattern): void {
        self::assertSame($expected, iterator_to_array(new BraceExpander($pattern)));
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return list<array{list<string>, string}>
     */
    public static function dataProviderGetIterable(): array {
        return [
            // Sequences
            [['abd', 'acd'], 'a{b,c}d'],
            [['ac', 'bc'], '{a,b}c'],
            [['ab', 'ac'], 'a{b,c}'],
            [['abd', 'ad', 'acd'], 'a{b,,c}d'],
            [['{abc}'], '{abc}'],
            [['/{abc}/'], '/{abc}/'],
            [['..', '..', '..'], '{..,..,..}'],
            [['..', '', '..'], '{..,,..}'],

            // Integer ranges
            [['1', '2', '3', '4', '5'], '{1..5}'],
            [['1', '3', '5'], '{1..5..2}'],
            [['1', '3', '5'], '{1..5..-2}'],
            [['5', '4', '3', '2', '1'], '{5..1}'],
            [['5', '3', '1'], '{5..1..2}'],
            [['5', '3', '1'], '{5..1..-2}'],
            [['-1', '-2', '-3', '-4', '-5'], '{-1..-5}'],
            [['-5', '-4', '-3', '-2', '-1'], '{-5..-1}'],
            [['-005', '-003', '-001'], '{-5..-001..2}'],
            [['01', '03', '05'], '{01..5..2}'],
            [['001', '021', '041', '061', '081'], '{01..100..20}'],

            // Character ranges
            [['a', 'b', 'c', 'd', 'e'], '{a..e}'],
            [['a', 'c', 'e'], '{a..e..2}'],
            [['a', 'c', 'e'], '{a..e..-2}'],
            [['e', 'd', 'c', 'b', 'a'], '{e..a}'],
            [['e', 'c', 'a'], '{e..a..2}'],
            [['e', 'c', 'a'], '{e..a..-2}'],

            // Mixed/Invalid ranges
            [['{1..a}'], '{1..a}'],
            [['{a..1}'], '{a..1}'],
            [['{1..2..a}'], '{1..2..a}'],
            [['{1..2..0.1}'], '{1..2..0.1}'],
            [['{a..b..c}'], '{a..b..c}'],
            [['{aa..b}'], '{aa..b}'],

            // Escaping
            [['a{b,c}d'], 'a{b\\,c}d'],
            [['a{b,c}d'], 'a\\{b,c}d'],
            [['a{b,c}d'], 'a{b,c\\}d'],
            [['{5..1}'], '{5\\..1}'],

            // Combinations
            [['{a', '{b'], '{{a,b}'],
            [['a-{bc}-e', 'a-{bd}-e'], 'a-{b{c,d}}-e'],
            [['a-{bc-d-f', 'a-{bc-e-f'], 'a-{bc-{d,e}-f'],
            [['A', 'B', 'C', 'a', 'b', 'c'], '{{A..C},{a..c}}'],
            [['a.php', 'a.json', 'b.php', 'b.json'], '{a,b}.{php,json}'],

            // Other
            [['a{'], 'a{'],
            [['a{{'], 'a{{'],
            [['a{{}'], 'a{{}'],
            [['{},a}b'], '{},a}b'],
            [['a{},b}c'], 'a{},b}c'], // differs from bash: `a}c` `abc`
            [['{1..3}', '{1..4}', '{2..3}', '{2..4}'], '{{1,2}..{3,4}}'],
            [['{a..1}', '{a..2}', '{b..1}', '{b..2}'], '{{a..b}..{1..2}}'],
            [['{a..1}', '{a..2}', '{b..1}', '{b..2}'], '{{a,b}..{1..2}}'],
            [['{a..1}', '{a..2}', '{b..1}', '{b..2}'], '{{a..b}..{1,2}}'],
            [['{1..3}', '{2..3}'], '{{1,2}..3}'],
            [['{3..1}', '{3..2}'], '{3..{1,2}}'],
            [['a', '../b'], '{a,../b}'],
            [['a..', '/b'], '{a..,/b}'],
            [['a..b', '/c'], '{a..b,/c}'],
            [['a', 'b../c'], '{a,b../c}'],
            [['1..2', '3..4'], '{1..2,3..4}'],
            [['1..2', '3'], '{1..2,3}'],
            [['1', '2..3'], '{1,2..3}'],
            [['/{..a}/'], '/{..a}/'],
            [['0{1..}2'], '0{1..}2'],
            [['{a..1..5}'], '{a..1..5}'],
            [['a{1..a}1', 'a{1..a}2', 'b{1..a}1', 'b{1..a}2'], '{a,b}{1..a}{1,2}'],
        ];
    }
    //</editor-fold>
}
