<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use LastDragon_ru\Path\DirectoryPath;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use SplFileInfo;

/**
 * @internal
 */
#[CoversClass(Glob::class)]
final class GlobTest extends TestCase {
    public function testMatch(): void {
        // Prepare
        $root = (new DirectoryPath(__DIR__))->normalized();
        $glob = new Glob($root, ['*.txt', '*.md', '**/*.tmp']);

        // Strings
        self::assertFalse($glob->match((string) $root->file('/file.txt')));
        self::assertTrue($glob->match((string) $root->file('file.md')));
        self::assertFalse($glob->match((string) $root->file('file.php')));
        self::assertFalse($glob->match((string) $root->file('a/file.md')));
        self::assertTrue($glob->match((string) $root->file('file.tmp')));
        self::assertTrue($glob->match((string) $root->file('a/file.tmp')));
        self::assertFalse($glob->match((string) $root->file('/a/file.tmp')));
        self::assertTrue($glob->match((string) $root->file('a/b/file.tmp')));
        self::assertFalse($glob->match((string) $root->file('/a/b/file.tmp')));

        // Path
        self::assertTrue($glob->match($root->file('file.md')));
        self::assertFalse($glob->match($root->directory('dir.md')));

        // Spl
        $dirInfo = Mockery::mock(SplFileInfo::class);
        $dirInfo
            ->shouldReceive('isDir')
            ->once()
            ->andReturn(true);
        $dirInfo
            ->shouldReceive('getPathname')
            ->once()
            ->andReturn(
                (string) $root->directory('dir.md'),
            );

        self::assertFalse($glob->match($dirInfo));

        $fileInfo = Mockery::mock(SplFileInfo::class);
        $fileInfo
            ->shouldReceive('isDir')
            ->once()
            ->andReturn(false);
        $fileInfo
            ->shouldReceive('getPathname')
            ->once()
            ->andReturn(
                (string) $root->file('dir.md'),
            );

        self::assertTrue($glob->match($fileInfo));
    }
}
