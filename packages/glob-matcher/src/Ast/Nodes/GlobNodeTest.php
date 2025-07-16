<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Ast\Nodes;

use LastDragon_ru\DiyParser\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Options;
use LastDragon_ru\GlobMatcher\Parser\Parser;
use LastDragon_ru\GlobMatcher\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(GlobNode::class)]
final class GlobNodeTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    #[DataProvider('dataProviderToRegex')]
    public function testToRegex(string $expected, string $pattern, Options $options): void {
        $node   = (new Parser($options))->parse($pattern);
        $actual = $node instanceof GlobNode
            ? $node::toRegex($options, new Cursor($node))
            : null;

        self::assertSame($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProvider">
    // =========================================================================
    /**
     * @return array<string, array{string, string, Options}>
     */
    public static function dataProviderToRegex(): array {
        return [
            'default'         => [
                '(?!\.)(?:(?=.)file\.txt)',
                'file.txt',
                new Options(),
            ],
            'hidden: true'    => [
                '(?!\.{1,2}(?:/|$))(?:(?=.)file\.txt)',
                'file.txt',
                new Options(hidden: true),
            ],
            'hidden: false'   => [
                '(?!\.)(?:(?=.)file\.txt)',
                'file.txt',
                new Options(hidden: false),
            ],
            'starts with dot' => [
                '(?!\.{1,2}(?:/|$))(?:(?=.)\.txt)',
                '.txt',
                new Options(),
            ],
            '../../..'        => [
                '(?:\.\.)(?:/)(?:\.\.)(?:/)(?:\.\.)',
                '../../..',
                new Options(),
            ],
            '*/.*'            => [
                '(?:(?!\.{1,2}(?:/|$))(?:(?=.)[^/]*?))(?:/)(?:(?!\.{1,2}(?:/|$))(?:(?=.)(?:\.)(?:[^/]*?)))',
                '*/.*',
                new Options(hidden: true),
            ],
        ];
    }
    // </editor-fold>
}
