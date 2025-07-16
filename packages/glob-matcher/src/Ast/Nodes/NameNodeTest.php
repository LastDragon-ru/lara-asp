<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Ast\Nodes;

use LastDragon_ru\DiyParser\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Options;
use LastDragon_ru\GlobMatcher\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(NameNode::class)]
final class NameNodeTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderToRegex')]
    public function testToRegex(string $expected, NameNode $node, Options $options): void {
        self::assertSame($expected, $node::toRegex($options, new Cursor($node)));
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<string, array{string, NameNode, Options}>
     */
    public static function dataProviderToRegex(): array {
        return [
            '`.` must be matched explicitly'  => [
                '\\.',
                new NameNode([new StringNode('.')]),
                new Options(),
            ],
            '`..` must be matched explicitly' => [
                '\\.\\.',
                new NameNode([new StringNode('..')]),
                new Options(),
            ],
            'hidden = default'                => [
                '(?!\.)(?:(?=.)abc)',
                new NameNode([new StringNode('abc')]),
                new Options(),
            ],
            'hidden = false'                  => [
                '(?!\.)(?:(?=.)abc)',
                new NameNode([new StringNode('abc')]),
                new Options(hidden: false),
            ],
            'hidden = true'                   => [
                '(?!\.{1,2}(?:/|$))(?:(?=.)abc)',
                new NameNode([new StringNode('abc')]),
                new Options(hidden: true),
            ],
            'hidden = explicit'               => [
                '(?!\.{1,2}(?:/|$))(?:(?=.)\.abc)',
                new NameNode([new StringNode('.abc')]),
                new Options(hidden: false),
            ],
        ];
    }
    // </editor-fold>
}
