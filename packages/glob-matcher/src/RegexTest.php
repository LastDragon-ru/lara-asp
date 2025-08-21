<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher;

use LastDragon_ru\GlobMatcher\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Regex::class)]
final class RegexTest extends TestCase {
    public function testIsMatch(): void {
        self::assertTrue((new Regex('abc', matchCase: false))->isMatch('ABC'));
        self::assertFalse((new Regex('b'))->isMatch('abc'));
        self::assertTrue((new Regex('b', MatchMode::Contains))->isMatch('abc'));
        self::assertFalse((new Regex('b', MatchMode::Starts))->isMatch('abc'));
        self::assertFalse((new Regex('b', MatchMode::Ends))->isMatch('abc'));
        self::assertFalse((new Regex('b'))->isMatch('abc'));
        self::assertTrue((new Regex('abc'))->isMatch('abc'));
        self::assertTrue((new Regex('a', MatchMode::Starts))->isMatch('abc'));
        self::assertTrue((new Regex('c', MatchMode::Ends))->isMatch('abc'));
    }

    public function testToString(): void {
        self::assertSame('#^pattern$#us', (string) new Regex('pattern'));
        self::assertSame('#^pattern$#usi', (string) new Regex('pattern', matchCase: false));
        self::assertSame('#^pattern$#us', (string) new Regex('pattern', MatchMode::Match));
        self::assertSame('#pattern#us', (string) new Regex('pattern', MatchMode::Contains));
        self::assertSame('#^pattern#us', (string) new Regex('pattern', MatchMode::Starts));
        self::assertSame('#pattern$#us', (string) new Regex('pattern', MatchMode::Ends));
    }
}
