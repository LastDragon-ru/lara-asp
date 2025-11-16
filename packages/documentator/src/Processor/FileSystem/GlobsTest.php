<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\FileSystem;

use LastDragon_ru\LaraASP\Core\Path\DirectoryPath;
use LastDragon_ru\LaraASP\Documentator\Package\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use SplFileInfo;

/**
 * @internal
 */
#[CoversClass(Globs::class)]
final class GlobsTest extends TestCase {
    public function testMatch(): void {
        // Prepare
        $root = (new DirectoryPath(__DIR__))->getNormalizedPath();
        $glob = new Globs($root, ['*.txt', '*.md', '**/*.tmp']);

        // Strings
        self::assertFalse($glob->match((string) $root->getFilePath('/file.txt')));
        self::assertTrue($glob->match((string) $root->getFilePath('file.md')));
        self::assertFalse($glob->match((string) $root->getFilePath('file.php')));
        self::assertFalse($glob->match((string) $root->getFilePath('a/file.md')));
        self::assertTrue($glob->match((string) $root->getFilePath('file.tmp')));
        self::assertTrue($glob->match((string) $root->getFilePath('a/file.tmp')));
        self::assertFalse($glob->match((string) $root->getFilePath('/a/file.tmp')));
        self::assertTrue($glob->match((string) $root->getFilePath('a/b/file.tmp')));
        self::assertFalse($glob->match((string) $root->getFilePath('/a/b/file.tmp')));

        // Path
        self::assertTrue($glob->match($root->getFilePath('file.md')));
        self::assertFalse($glob->match($root->getDirectoryPath('dir.md')));

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
                (string) $root->getDirectoryPath('dir.md'),
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
                (string) $root->getDirectoryPath('dir.md'),
            );

        self::assertTrue($glob->match($fileInfo));
    }
}
