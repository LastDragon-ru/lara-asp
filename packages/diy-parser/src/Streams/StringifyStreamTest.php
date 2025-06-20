<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Streams;

use LastDragon_ru\DiyParser\Testing\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use Stringable;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(StringifyStream::class)]
final class StringifyStreamTest extends TestCase {
    public function testGetIterator(): void {
        $tokens = [
            1     => 'a',
            2     => StringifyStreamTest_Enum::B,
            3     => StringifyStreamTest_Enum::C,
            4     => 'd',
            5     => null,
            'key' => new class() implements Stringable {
                #[Override]
                public function __toString(): string {
                    return Stringable::class;
                }
            },
            10    => 123,
        ];

        self::assertSame(
            [
                1     => 'a',
                2     => 'B',
                3     => 'C',
                4     => 'd',
                5     => '',
                'key' => Stringable::class,
                10    => '123',
            ],
            iterator_to_array(
                new StringifyStream($tokens),
            ),
        );
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

enum StringifyStreamTest_Enum: string {
    case B = 'B';
    case C = 'C';
}
