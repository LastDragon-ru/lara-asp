<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Formatter\Utils;

use LastDragon_ru\LaraASP\Formatter\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

use function array_map;
use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(UnicodeDateTimeFormatParser::class)]
final class UnicodeDateTimeFormatParserTest extends TestCase {
    // <editor-fold desc="Tests">
    // =========================================================================
    /**
     * @param array<array-key, array{string, string}> $expected
     */
    #[DataProvider('dataProviderGetIterator')]
    public function testGetIterator(array $expected, string $format): void {
        $actual = iterator_to_array(new UnicodeDateTimeFormatParser($format), false);
        $actual = array_map(static fn (UnicodeDateTimeFormatToken $token) => [$token->pattern, $token->value], $actual);

        self::assertEquals($expected, $actual);
    }
    // </editor-fold>

    // <editor-fold desc="DataProviders">
    // =========================================================================
    /**
     * @return array<string, array{array<array-key, array{string, string}>, string}>
     */
    public static function dataProviderGetIterator(): array {
        return [
            'a' => [[], ''],
            'b' => [
                [
                    ["'", 'text'],
                ],
                "'text'",
            ],
            'c' => [
                [
                    ['H', 'HH'],
                    ["'", ':'],
                    ['m', 'mm'],
                    ["'", ':'],
                    ['s', 'ss'],
                    ["'", '.'],
                    ['S', 'SSS'],
                ],
                'HH:mm:ss.SSS',
            ],
            'd' => [
                [
                    ['H', 'HH'],
                    ["'", ":'"],
                    ['m', 'mm'],
                    ["'", ":ss'"],
                ],
                "HH:''mm:'ss'''",
            ],
            'e' => [
                [
                    ["'", "''mm"],
                    ['s', 'sss'],
                    ["'", "'"],
                ],
                "'''''mm'sss''",
            ],
            'f' => [
                [
                    ["'", 'абвгдеё;%:'],
                    ['Y', 'Y'],
                    ["'", '😀'],
                    ['y', 'yyyy'],
                ],
                'абвгдеё;%:Y😀yyyy',
            ],
        ];
    }
    // </editor-fold>
}
