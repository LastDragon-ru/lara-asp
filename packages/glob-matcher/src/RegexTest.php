<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher;

use LastDragon_ru\GlobMatcher\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Regex::class)]
final class RegexTest extends TestCase {
    public function testMatch(): void {
        self::assertTrue((new Regex('abc', matchCase: false))->match('ABC'));
        self::assertFalse((new Regex('b'))->match('abc'));
        self::assertTrue((new Regex('b', MatchMode::Contains))->match('abc'));
        self::assertFalse((new Regex('b', MatchMode::Starts))->match('abc'));
        self::assertFalse((new Regex('b', MatchMode::Ends))->match('abc'));
        self::assertFalse((new Regex('b'))->match('abc'));
        self::assertTrue((new Regex('abc'))->match('abc'));
        self::assertTrue((new Regex('a', MatchMode::Starts))->match('abc'));
        self::assertTrue((new Regex('c', MatchMode::Ends))->match('abc'));
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
