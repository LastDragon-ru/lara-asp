<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Streams;

use LastDragon_ru\DiyParser\Testing\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function implode;
use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(StringSplitStream::class)]
final class StringSplitStreamTest extends TestCase {
    public function testGetIterator(): void {
        // Prepare
        $separators = ['[', '!!!', ']['];
        $strings    = [
            'aaa][aaaaaaaa]',
            '[bbbbbbbbbbb!!',
            '!ccccc[',
            'cccccc',
        ];
        $string     = implode('', $strings);

        // Long buffer
        $long = iterator_to_array(
            new StringSplitStream($strings, $separators),
        );

        self::assertSame($string, implode('', $long));
        self::assertSame(
            [
                0  => 'aaa',
                3  => '][',
                5  => 'aaaaaaaa',
                13 => '][',
                15 => 'bbbbbbbbbbb',
                26 => '!!!',
                29 => 'ccccc',
                34 => '[',
                35 => 'cccccc',
            ],
            $long,
        );

        // Short buffer
        $short = iterator_to_array(
            new StringSplitStream($strings, $separators, 2),
        );

        self::assertSame($string, implode('', $short));
        self::assertSame(
            [
                0  => 'aaa',
                3  => '][',
                5  => 'aaaaaaaa',
                13 => '][',
                15 => 'bbbbbbbbbbb',
                26 => '!!!',
                29 => 'ccccc',
                34 => '[',
                35 => 'cccccc',
            ],
            $short,
        );
    }

    public function testGetIteratorCaseInsensitive(): void {
        $separators = ['a'];
        $input      = ['A[a]A'];
        $expected   = ['A', '[', 'a', ']', 'A'];

        self::assertSame(
            $expected,
            iterator_to_array(
                new StringSplitStream($input, $separators, caseSensitive: false),
            ),
        );
    }
}
