<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\LaraASP\Core\Path\FilePath;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Globs::class)]
final class GlobsTest extends TestCase {
    public function testIsMatch(): void {
        $globs = new Globs(['*.txt', '*.md']);

        self::assertTrue($globs->isMatch(new FilePath('file.txt')));
        self::assertTrue($globs->isMatch(new FilePath('file.md')));
        self::assertFalse($globs->isMatch(new FilePath('file.php')));
    }

    public function testToRegexp(): void {
        self::assertNull((new Globs([]))->regexp);
        self::assertSame(
            '#(^(?=[^\.])[^/]*\.txt$)|(^(?=[^\.])[^/]*\.md$)#u',
            (new Globs(['*.txt', '*.md']))->regexp,
        );
        self::assertSame(
            '#(^(?=[^\.])[^/]*[^/]*/(?=[^\.])[^/]*\.txt$)|(^(?=[^\.])[^/]*[^/]*/(?=[^\.])[^/]*\.md$)#u',
            (new Globs(['**/*.txt', '**/*.md']))->regexp,
        );
    }
}
