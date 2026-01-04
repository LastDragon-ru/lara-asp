<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\Path\DirectoryPath;
use LastDragon_ru\Path\FilePath;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(Glob::class)]
final class GlobTest extends TestCase {
    public function testMatch(): void {
        // Prepare
        $glob = new Glob(['*.txt', '*.md', '**/*.tmp'], false);

        // Strings
        self::assertFalse($glob->match('/file.txt'));
        self::assertTrue($glob->match('file.md'));
        self::assertFalse($glob->match('file.php'));
        self::assertFalse($glob->match('a/file.md'));
        self::assertTrue($glob->match('file.tmp'));
        self::assertTrue($glob->match('a/file.tmp'));
        self::assertTrue($glob->match('/a/file.tmp'));
        self::assertTrue($glob->match('a/b/file.tmp'));
        self::assertTrue($glob->match('/a/b/file.tmp'));

        // Path
        self::assertTrue($glob->match(new FilePath('file.md')));
        self::assertTrue($glob->match(new DirectoryPath('dir.md')));

        // Hidden
        self::assertFalse((new Glob(['**/*.tmp'], false))->match('/a/.file.tmp'));
        self::assertTrue((new Glob(['**/*.tmp'], true))->match('/a/.file.tmp'));
    }
}
