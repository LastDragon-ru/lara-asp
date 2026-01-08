<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Iterables;

use LastDragon_ru\TextParser\Package\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use Stringable;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(StringifyIterable::class)]
final class StringifyIterableTest extends TestCase {
    public function testGetIterator(): void {
        $tokens = [
            1     => 'a',
            2     => StringifyIterableTest_Enum::B,
            3     => StringifyIterableTest_Enum::C,
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
                new StringifyIterable($tokens),
            ),
        );
    }
}

// @phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
// @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

enum StringifyIterableTest_Enum: string {
    case B = 'B';
    case C = 'C';
}
